<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminStatsResource; // استدعاء الريسورس
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalSales = Order::where('status', 'delivered')
            ->orWhere('payment_status', 'paid')
            ->sum('total');

        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $completedOrdersCount = Order::where('status', 'delivered')->count();

        $topSellingProducts = OrderItem::select('product_variant_id', DB::raw('SUM(quantity) as total_qty'))
            ->with(['variant.product'])
            ->groupBy('product_variant_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'variant_id'   => $item->product_variant_id,
                    'product_name' => $item->variant->product->name ?? 'منتج غير معروف',
                    'sku'          => $item->variant->sku ?? '',
                    'total_sold'   => (int) $item->total_qty
                ];
            });

        // تجميع الداتا وتمريرها للريسورس الملكي
        $statsData = [
            'total_sales'      => $totalSales,
            'pending_orders'   => $pendingOrdersCount,
            'completed_orders' => $completedOrdersCount,
            'top_products'     => $topSellingProducts,
        ];

        return new AdminStatsResource($statsData);
    }
}
