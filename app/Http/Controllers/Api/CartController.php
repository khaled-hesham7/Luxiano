<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartStoreRequest;
use App\Http\Resources\CartResource; // الـ Resource الجديد
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
}
