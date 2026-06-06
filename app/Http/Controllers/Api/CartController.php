<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartStoreRequest;
use App\Http\Resources\CartResource; // الـ Resource الجديد
use App\Models\Coupon;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    private function getCartKey(Request $request)
    {
        $identifier = $request->user() ? $request->user()->id : $request->ip();
        return "luxiano_cart_" . $identifier;
    }

    // 1. عرض محتويات السلة باستخدام الـ API Resource
    public function index(Request $request)
    {
        $cartKey = $this->getCartKey($request);
        $cart = Cache::get($cartKey, []);

        $variants = [];
        $cartTotal = 0;

        if (!empty($cart)) {
            // جلب كل الـ Variants المذكورة في السلة مرة واحدة من الداتابيز (Eager Loading)
            $variantModels = ProductVariant::with(['product', 'attributeValues.attribute'])
                ->whereIn('id', array_keys($cart))
                ->get();

            foreach ($variantModels as $variant) {
                // بنحط حقل ديناميكي جوه الموديل طياري عشان الريسورس يقراه
                $variant->cart_quantity = $cart[$variant->id];

                $unitPrice = $variant->price ?? $variant->product->base_price;
                $cartTotal += $unitPrice * $variant->cart_quantity;

                $variants[] = $variant;
            }
        }

        return response()->json([
            'cart_total' => $cartTotal,
            // هنا بنباصي الـ Collection للريسورس والـ JSON هيطلع مسطرة
            'items'      => CartResource::collection($variants)
        ], 200);
    }

    // 2. إضافة منتج للسلة
    public function store(CartStoreRequest $request)
    {
        $cartKey = $this->getCartKey($request);
        $cart = Cache::get($cartKey, []);

        $variantId = $request->product_variant_id;
        $quantity = $request->quantity;

        if (array_key_exists($variantId, $cart)) {
            $variant = ProductVariant::find($variantId);
            if (($cart[$variantId] + $quantity) > $variant->stock) {
                return response()->json(['message' => 'عفواً، الكمية الإضافية تتخطى المتاح في المخزن'], 422);
            }
            $cart[$variantId] += $quantity;
        } else {
            $cart[$variantId] = $quantity;
        }

        Cache::put($cartKey, $cart, now()->addDays(1));

        return response()->json([
            'message' => 'تم إضافة المنتج إلى السلة بنجاح',
            'cart_summary' => $cart
        ], 200);
    }

    // 3. حذف قطعة من السلة
    public function destroy(Request $request, $variant_id)
    {
        $cartKey = $this->getCartKey($request);
        $cart = Cache::get($cartKey, []);

        if (array_key_exists($variant_id, $cart)) {
            unset($cart[$variant_id]);
            Cache::put($cartKey, $cart, now()->addDays(1));
            return response()->json(['message' => 'تم حذف القطعة من السلة بنجاح'], 200);
        }

        return response()->json(['message' => 'المنتج غير موجود في السلة'], 404);
    }

    // 4. تطبيق كوبون الخصم على السلة وتوقع الحسابات (Apply Coupon)
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code'
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)->first();

        // جلب السلة الحالية لحساب الإجمالي
        $cartKey = $this->getCartKey($request);
        $cart = Cache::get($cartKey, []);

        if (empty($cart)) {
            return response()->json(['message' => 'عفواً، سلة التسوق فارغة لا يمكن تطبيق الخصم.'], 422);
        }

        $subtotal = 0;
        $variantModels = ProductVariant::with('product')
            ->whereIn('id', array_keys($cart))
            ->get();

        foreach ($variantModels as $variant) {
            $unitPrice = $variant->price ?? $variant->product->base_price;
            $subtotal += $unitPrice * $cart[$variant->id];
        }

        // فحص صلاحية الكوبون مع الإجمالي الحالي للطلب
        if (!$coupon->isValid($subtotal)) {
            return response()->json([
                'message' => 'عفواً، كود الخصم هذا غير صالح للاستخدام حالياً أو لم يصل للحد الأدنى المطلوب.'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $total = $subtotal - $discount;

        return response()->json([
            'message'             => 'تم تطبيق الكوبون بنجاح!',
            'coupon_code'         => $coupon->code,
            'discount_type'       => $coupon->type,
            'discount_value'      => (float) $coupon->value,
            'original_subtotal'   => $subtotal,
            'discount_amount'     => $discount,
            'discounted_subtotal' => $total
        ], 200);
    }

    // 5. تحديث كمية منتج معين في سلة التسوق
    public function update(Request $request, $variant_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartKey = $this->getCartKey($request);
        $cart = Cache::get($cartKey, []);

        if (!array_key_exists($variant_id, $cart)) {
            return response()->json(['message' => 'المنتج غير موجود في السلة حالياً.'], 404);
        }

        // التحقق من توفر الكمية المطلوبة في المخزن
        $variant = ProductVariant::find($variant_id);
        if (!$variant || $request->quantity > $variant->stock) {
            return response()->json([
                'message' => "عفواً، الكمية المطلوبة غير متوفرة في المخزن. المتاح حالياً هو: {$variant->stock} قطع فقط."
            ], 422);
        }

        // تحديث الكمية وحفظ الكاش
        $cart[$variant_id] = $request->quantity;
        Cache::put($cartKey, $cart, now()->addDays(1));

        return response()->json([
            'message'      => 'تم تحديث كمية المنتج في السلة بنجاح',
            'cart_summary' => $cart
        ], 200);
    }
}
