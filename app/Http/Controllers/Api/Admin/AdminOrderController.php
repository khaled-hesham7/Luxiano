<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateOrderStatusRequest; // الـ Form Request الجديد
use App\Http\Resources\OrderResource; // بنستخدم نفس ريسورس الطلبات النضيف
use App\Models\Order;
use Illuminate\Support\Facades\DB;


class AdminOrderController extends Controller
{
    // عرض كل طلبات زبائن الموقع
    public function index()
    {
        $orders = Order::with(['user', 'address', 'items.variant.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    // تحديث الحالة بحماية وفاليديشن عالي
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        $order = Order::with('items.variant')->findOrFail($id);

        // البيزنس لوجيك: لو اتلغى، رجّع الهدوم للمخزن فورا
        if ($order->status !== 'cancelled' && $request->status === 'cancelled') {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock', $item->quantity);
                    }
                }
            });
        }

        $order->update([
            'status'         => $request->status,
            'payment_status' => $request->payment_status,
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح وسجل المخزن مضبوط',
            'order'   => new OrderResource($order->load(['items.variant.product', 'address']))
        ], 200);
    }
}
