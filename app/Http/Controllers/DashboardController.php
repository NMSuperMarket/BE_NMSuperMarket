<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $today = Carbon::today();
        
        // Doanh thu hôm nay (Chỉ tính các đơn hàng không bị hủy)
        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Số đơn hàng mới hôm nay
        $newOrdersCount = Order::whereDate('created_at', $today)->count();

        // Đơn hàng chờ xử lý
        $pendingOrdersCount = Order::where('status', 'pending')->count();

        // Sản phẩm sắp hết hàng (stock <= 10)
        $lowStockCount = Inventory::where('quantity', '<=', 10)->count();

        // Doanh thu 7 ngày qua
        $revenueLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->where('status', '!=', 'cancelled')
                ->sum('total');
            
            $revenueLast7Days[] = [
                'name' => $date->format('d/m'),
                'doanhThu' => $revenue
            ];
        }

        // Top 5 sản phẩm bán chạy nhất
        $topProducts = Product::orderBy('sold_count', 'desc')->take(5)->get(['id', 'name', 'sold_count', 'price', 'thumbnail']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'today_revenue' => $todayRevenue,
                    'new_orders' => $newOrdersCount,
                    'pending_orders' => $pendingOrdersCount,
                    'low_stock' => $lowStockCount,
                ],
                'chart' => $revenueLast7Days,
                'top_products' => $topProducts
            ]
        ]);
    }
}
