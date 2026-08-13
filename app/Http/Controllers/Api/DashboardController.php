<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Counts
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalCustomers = Customer::count();

        $totalSuppliers = Supplier::count();

        $totalOrders = Order::count();

        $totalSales = Sale::count();

        $totalPurchases = Purchase::count();

        $totalDeliveries = Delivery::count();

        /*
        |--------------------------------------------------------------------------
        | Sales Statistics
        |--------------------------------------------------------------------------
        */

        $totalSalesAmount = Sale::sum('grand_total');

        $todaySalesAmount = Sale::whereDate(
            'created_at',
            today()
        )->sum('grand_total');

        $todaySalesCount = Sale::whereDate(
            'created_at',
            today()
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Payment Statistics
        |--------------------------------------------------------------------------
        */

        $totalPayments = Payment::where('status', 'completed')
            ->sum('amount');

        $todayPayments = Payment::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Order Statistics
        |--------------------------------------------------------------------------
        */

        $pendingOrders = Order::where('status', 'pending')->count();

        $processingOrders = Order::where('status', 'processing')->count();

        $completedOrders = Order::where('status', 'completed')->count();

        /*
        |--------------------------------------------------------------------------
        | Delivery Statistics
        |--------------------------------------------------------------------------
        */

        $pendingDeliveries = Delivery::where(
            'status',
            'pending'
        )->count();

        $assignedDeliveries = Delivery::where(
            'status',
            'assigned'
        )->count();

        $completedDeliveries = Delivery::where(
            'status',
            'delivered'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Inventory Statistics
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Product::whereColumn(
            'stock_quantity',
            '<=',
            'reorder_level'
        )->count();

        $outOfStockProducts = Product::where(
            'stock_quantity',
            '<=',
            0
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::with([
            'customer',
            'items.product'
        ])
        ->latest()
        ->take(5)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Sales
        |--------------------------------------------------------------------------
        */

        $recentSales = Sale::with([
            'customer',
            'items.product'
        ])
        ->latest()
        ->take(5)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Payments
        |--------------------------------------------------------------------------
        */

        $recentPayments = Payment::with([
            'sale',
            'user'
        ])
        ->latest()
        ->take(5)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Low Stock Products
        |--------------------------------------------------------------------------
        */

        $lowStockItems = Product::whereColumn(
            'stock_quantity',
            '<=',
            'reorder_level'
        )
        ->orderBy('stock_quantity')
        ->take(10)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Sales By Payment Method
        |--------------------------------------------------------------------------
        */

        $salesByPaymentMethod = Payment::where(
            'status',
            'completed'
        )
        ->select(
            'payment_method',
            DB::raw('SUM(amount) as total')
        )
        ->groupBy('payment_method')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'Dashboard data retrieved successfully.',

            'summary' => [
                'total_products' => $totalProducts,
                'total_customers' => $totalCustomers,
                'total_suppliers' => $totalSuppliers,
                'total_orders' => $totalOrders,
                'total_sales' => $totalSales,
                'total_purchases' => $totalPurchases,
                'total_deliveries' => $totalDeliveries,
            ],

            'sales' => [
                'total_amount' => $totalSalesAmount,
                'today_amount' => $todaySalesAmount,
                'today_count' => $todaySalesCount,
            ],

            'payments' => [
                'total_received' => $totalPayments,
                'today_received' => $todayPayments,
            ],

            'orders' => [
                'pending' => $pendingOrders,
                'processing' => $processingOrders,
                'completed' => $completedOrders,
            ],

            'deliveries' => [
                'pending' => $pendingDeliveries,
                'assigned' => $assignedDeliveries,
                'delivered' => $completedDeliveries,
            ],

            'inventory' => [
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
            ],

            'sales_by_payment_method' => $salesByPaymentMethod,

            'low_stock_items' => $lowStockItems,

            'recent_orders' => $recentOrders,

            'recent_sales' => $recentSales,

            'recent_payments' => $recentPayments,
        ]);
    }
}