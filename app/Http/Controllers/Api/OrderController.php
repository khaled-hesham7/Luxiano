<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Http\Resources\OrderResource; // استدعاء الريسورس الجديد هنا
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(OrderStoreRequest $request)
    {
        $user = $request->user(); // جلب العميل المسجل (لازم التوكن هنا)

        // 1. جلب السلة من الكاش
        // 1. جلب السلة من الكاش (بنجرب بالـ User ID الأول، ولو فاضية بنجرب بالـ IP)
        $cartKey = "luxiano_cart_" . $user->id;
        $cart = Cache::get($cartKey, []);

        if (empty($cart)) {
            $cartKey = "luxiano_cart_" . $request->ip();
            $cart = Cache::get($cartKey, []);
        }

        if (empty($cart)) {
            return response()->json(['message' => 'عفواً، سلة التسوق فارغة لا يمكن إتمام الطلب'], 422);
        }

        // فتح الترانزأكشن لحماية البيانات منعاً للـ Data Race
        DB::beginTransaction();

        try {
            $subtotal = 0;
            $orderItemsData = [];

            // 2. فحص المخزن وحساب الأسعار حاسوبياً من الباك إيند
            foreach ($cart as $variantId => $quantity) {
                // عمل Lock للسطر لمنع شراء نفس القطعة من عميلين في نفس اللحظة والكمية المتاحة 1
                $variant = ProductVariant::lockForUpdate()->find($variantId);

                if (!$variant || $variant->stock < $quantity) {
                    return response()->json([
                        'message' => "عفواً، المنتج ذو الكود {$variant->sku} لم يعد متوفراً بالكمية المطلوبة في المخزن."
                    ], 422);
                }

                $price = $variant->price ?? $variant->product->base_price;
                $subtotal += $price * $quantity;

                // تجهيز الداتا في الـ Array لرفعها دفعة واحدة
                $orderItemsData[] = [
                    'product_variant_id' => $variant->id,
                    'quantity'           => $quantity,
                    'price'              => $price,
                    'variant_model'      => $variant // الـ Model المطلوب للـ Decrement لاحقاً
                ];
            }

            // جلب العنوان لحساب تكلفة الشحن ديناميكياً بناءً على المحافظة
            $address = Address::find($request->address_id);
            $shippingCost = $this->calculateShippingCost($address->governorate);

            // فحص وحساب خصم الكوبون لو تم إرساله
            $discount = 0.00;
            $coupon = null;
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();
                if (!$coupon || !$coupon->isValid($subtotal)) {
                    return response()->json([
                        'message' => 'عفواً، كود الخصم هذا غير صالح للاستخدام حالياً أو لم يصل للحد الأدنى المطلوب.'
                    ], 422);
                }
                $discount = $coupon->calculateDiscount($subtotal);
            }

            $total = $subtotal - $discount + $shippingCost;

            // 3. إنشاء الطلب الرئيسي في جدول orders
            $order = Order::create([
                'user_id'        => $user->id,
                'address_id'     => $request->address_id,
                'order_number'   => 'LUX-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'coupon_code'    => $coupon ? $coupon->code : null,
                'subtotal'       => $subtotal,
                'shipping_cost'  => $shippingCost,
                'discount'       => $discount,
                'total'          => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'status'         => 'pending'
            ]);

            // زيادة عداد استخدام الكوبون
            if ($coupon) {
                $coupon->increment('usage_count');
            }

            // 4. تسجيل عناصر الطلب وتنزيل المخزن فعلياً
            foreach ($orderItemsData as $item) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'price'              => $item['price']
                ]);

                // السطر السحري: تنزيل الكمية من مخزن الـ Variant فوراً
                $item['variant_model']->decrement('stock', $item['quantity']);
            }

            // 5. مسح سلة الـ Cache تماماً لنجاح العملية
            Cache::forget($cartKey);

            // تأكيد حفظ كل العمليات السابقة في قاعدة البيانات معاً
            DB::commit();

            // 6. الإرجاع الاحترافي للمستند بالكامل عبر الـ OrderResource ومحمل بكافة علاقاته الـ Eager Loaded
            return response()->json([
                'message' => 'تم تسجيل طلبك بنجاح، جاري تجهيز الشحنة الرائعة لك!',
                'order'   => new OrderResource($order->load(['items.variant.product', 'items.variant.attributeValues', 'address']))
            ], 201);
        } catch (\Exception $e) {
            // عمل Rollback فوري وإلغاء كافة السطور التي سجلت لو انقطع الاتصال أو حدث خطأ
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ ما أثناء معالجة الطلب، يرجى المحاولة مرة أخرى',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // عرض قائمة الأوردرات الخاصة بالعميل الحالي
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.variant.product', 'items.variant.attributeValues', 'address'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    // عرض تفاصيل أوردر محدد يخص العميل الحالي
    public function show(Request $request, $id)
    {
        $order = $request->user()->orders()
            ->with(['items.variant.product', 'items.variant.attributeValues', 'address'])
            ->findOrFail($id);

        return new OrderResource($order);
    }

    /**
     * حساب تكلفة الشحن بناءً على المحافظة من الداتابيز.
     */
    private function calculateShippingCost(string $governorate): float
    {
        $governorateKey = strtolower(trim($governorate));

        // الاستعلام عن السعر المخصص للمحافظة في الداتابيز
        $rate = ShippingRate::where('governorate', $governorateKey)->first();

        return $rate ? (float) $rate->cost : 70.00; // 70 جنيه كشحن افتراضي لو المحافظة غير مسجلة
    }
}
