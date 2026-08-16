<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 30);

        $revenue = Order::where('status', '!=', 'cancelled')->whereBetween('created_at', [now()->subDays($days), now()])->sum('total');
        $ordersCount = Order::whereBetween('created_at', [now()->subDays($days), now()])->count();
        $customers = User::where('is_admin', false)->count();
        $productsCount = Product::count();
        $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count();
        $lowStock = Product::whereColumn('stock', '<=', 'low_stock_threshold')->where('is_active', true)->count();
        $avgOrderValue = $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0;

        // sales chart (last N days)
        $chart = collect(range($days - 1, 0))->map(function ($i) {
            $day = now()->subDays($i)->startOfDay();
            $sales = Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$day, $day->copy()->endOfDay()])
                ->sum('total');
            $orders = Order::whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count();
            return [
                'date' => $day->format('M j'),
                'sales' => round((float) $sales, 2),
                'orders' => (int) $orders,
            ];
        })->values();

        // orders by status
        $statusCounts = Order::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        // recent orders
        $recentOrders = Order::withCount('items')->with('user:id,name')->latest()->limit(8)->get();

        // top products
        $topProducts = Product::orderByDesc('sold_count')->limit(5)->get(['id', 'name', 'sold_count', 'stock', 'price']);

        // recent reviews
        $recentReviews = Review::with('user:id,name')->with('product:id,name')->latest()->limit(6)->get();

        return response()->json([
            'stats' => [
                'revenue' => round($revenue, 2),
                'orders' => $ordersCount,
                'customers' => $customers,
                'products' => $productsCount,
                'pending_orders' => $pendingOrders,
                'low_stock' => $lowStock,
                'avg_order_value' => $avgOrderValue,
            ],
            'chart' => $chart,
            'status_counts' => $statusCounts,
            'recent_orders' => $recentOrders->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer' => $o->user?->name ?? 'Guest',
                'status' => $o->status,
                'total' => $o->total,
                'items_count' => $o->items_count,
                'created_at' => $o->created_at?->diffForHumans(),
            ]),
            'top_products' => $topProducts,
            'recent_reviews' => $recentReviews->map(fn ($r) => [
                'id' => $r->id,
                'user' => $r->user?->name,
                'product' => $r->product?->name,
                'rating' => $r->rating,
                'approved' => $r->is_approved,
                'created_at' => $r->created_at?->diffForHumans(),
            ]),
        ]);
    }
}
