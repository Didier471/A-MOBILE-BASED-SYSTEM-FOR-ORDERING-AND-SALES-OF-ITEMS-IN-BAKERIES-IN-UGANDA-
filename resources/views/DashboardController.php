<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $role = $user->getRoleNames()->first() ?? 'User';

        $base = [
            'role' => $role,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ];

        switch ($role) {

            case 'Admin':
                return response()->json(
                    array_merge(
                        $base,
                        $this->adminDashboard()
                    )
                );

            case 'Manager':
                return response()->json(
                    array_merge(
                        $base,
                        $this->managerDashboard()
                    )
                );

            case 'Sales Staff':
                return response()->json(
                    array_merge(
                        $base,
                        $this->salesStaffDashboard()
                    )
                );

            case 'Inventory Staff':
                return response()->json(
                    array_merge(
                        $base,
                        $this->inventoryStaffDashboard()
                    )
                );

            case 'Procurement Staff':
                return response()->json(
                    array_merge(
                        $base,
                        $this->procurementStaffDashboard()
                    )
                );

            case 'Delivery Staff':
                return response()->json(
                    array_merge(
                        $base,
                        $this->deliveryStaffDashboard()
                    )
                );

            default:
                return response()->json(
                    array_merge(
                        $base,
                        [
                            'dashboard' => 'default',
                            'summary' => $this->basicSummary(),
                        ]
                    )
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function adminDashboard(): array
    {
        $today = now()->startOfDay();

        $salesTotal = Sale::sum('total_amount');

        $todaySales = Sale::where(
            'created_at',
            '>=',
            $today
        )->sum('total_amount');

        $todaySalesCount = Sale::where(
            'created_at',
            '>=',
            $today
        )->count();


        $paymentsTotal = Payment::sum('amount');

        $todayPayments = Payment::where(
            'created_at',
            '>=',
            $today
        )->sum('amount');


        $purchasesTotal = Purchase::sum('total_amount');

        $todayPurchases = Purchase::where(
            'created_at',
            '>=',
            $today
        )->sum('total_amount');

        $todayPurchasesCount = Purchase::where(
            'created_at',
            '>=',
            $today
        )->count();


        return [
            'dashboard' => 'admin',

            'summary' => [
                'total_products' => Product::count(),
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
                'total_orders' => Order::count(),
                'total_sales' => Sale::count(),
                'total_purchases' => Purchase::count(),
                'total_deliveries' => Delivery::count(),
            ],

            'sales' => [
                'total_amount' => $salesTotal,
                'today_amount' => $todaySales,
                'today_count' => $todaySalesCount,
            ],

            'payments' => [
                'total_received' => $paymentsTotal,
                'today_received' => $todayPayments,
            ],

            'purchases' => [
                'total_amount' => $purchasesTotal,
                'today_amount' => $todayPurchases,
                'today_count' => $todayPurchasesCount,
            ],

            'orders' => [
                'pending' => Order::where(
                    'status',
                    'pending'
                )->count(),

                'processing' => Order::where(
                    'status',
                    'processing'
                )->count(),

                'completed' => Order::where(
                    'status',
                    'completed'
                )->count(),
            ],

            'deliveries' => [
                'pending' => Delivery::where(
                    'status',
                    'pending'
                )->count(),

                'assigned' => Delivery::where(
                    'status',
                    'assigned'
                )->count(),

                'delivered' => Delivery::where(
                    'status',
                    'delivered'
                )->count(),
            ],

            'inventory' => [
                'low_stock' => Product::whereColumn(
                    'stock_quantity',
                    '<=',
                    'reorder_level'
                )
                    ->where(
                        'stock_quantity',
                        '>',
                        0
                    )
                    ->count(),

                'out_of_stock' => Product::where(
                    'stock_quantity',
                    '<=',
                    0
                )->count(),
            ],

            'sales_by_payment_method' => Payment::select(
                'payment_method',
                DB::raw('SUM(amount) as total')
            )
                ->groupBy('payment_method')
                ->get(),

            'low_stock_items' => Product::whereColumn(
                'stock_quantity',
                '<=',
                'reorder_level'
            )
                ->orderBy('stock_quantity')
                ->get([
                    'id',
                    'name',
                    'sku',
                    'stock_quantity',
                    'reorder_level',
                ]),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MANAGER DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function managerDashboard(): array
    {
        $today = now()->startOfDay();

        $salesTotal = Sale::sum('total_amount');

        $todaySales = Sale::where(
            'created_at',
            '>=',
            $today
        )->sum('total_amount');

        $todaySalesCount = Sale::where(
            'created_at',
            '>=',
            $today
        )->count();


        $paymentsTotal = Payment::sum('amount');

        $todayPayments = Payment::where(
            'created_at',
            '>=',
            $today
        )->sum('amount');


        $purchasesTotal = Purchase::sum('total_amount');

        $todayPurchases = Purchase::where(
            'created_at',
            '>=',
            $today
        )->sum('total_amount');

        $todayPurchasesCount = Purchase::where(
            'created_at',
            '>=',
            $today
        )->count();


        return [
            'dashboard' => 'manager',

            'summary' => [
                'products' => Product::count(),
                'customers' => Customer::count(),
                'orders' => Order::count(),
                'suppliers' => Supplier::count(),
                'purchases' => Purchase::count(),
                'deliveries' => Delivery::count(),
            ],

            'sales' => [
                'total_amount' => $salesTotal,
                'today_amount' => $todaySales,
                'today_count' => $todaySalesCount,
            ],

            'payments' => [
                'total_received' => $paymentsTotal,
                'today_received' => $todayPayments,
            ],

            'purchases' => [
                'total_amount' => $purchasesTotal,
                'today_amount' => $todayPurchases,
                'today_count' => $todayPurchasesCount,
            ],

            'orders' => [
                'pending' => Order::where(
                    'status',
                    'pending'
                )->count(),

                'processing' => Order::where(
                    'status',
                    'processing'
                )->count(),

                'completed' => Order::where(
                    'status',
                    'completed'
                )->count(),
            ],

            'deliveries' => [
                'pending' => Delivery::where(
                    'status',
                    'pending'
                )->count(),

                'assigned' => Delivery::where(
                    'status',
                    'assigned'
                )->count(),

                'delivered' => Delivery::where(
                    'status',
                    'delivered'
                )->count(),
            ],

            'inventory' => [
                'low_stock' => Product::whereColumn(
                    'stock_quantity',
                    '<=',
                    'reorder_level'
                )
                    ->where(
                        'stock_quantity',
                        '>',
                        0
                    )
                    ->count(),

                'out_of_stock' => Product::where(
                    'stock_quantity',
                    '<=',
                    0
                )->count(),
            ],

            'low_stock_items' => Product::whereColumn(
                'stock_quantity',
                '<=',
                'reorder_level'
            )
                ->orderBy('stock_quantity')
                ->get([
                    'id',
                    'name',
                    'sku',
                    'stock_quantity',
                    'reorder_level',
                ]),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SALES STAFF DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function salesStaffDashboard(): array
    {
        $today = now()->startOfDay();


        return [
            'dashboard' => 'sales_staff',

            'summary' => [
                'orders' => Order::count(),
                'sales' => Sale::count(),
                'customers' => Customer::count(),
            ],

            'today' => [
                'orders' => Order::where(
                    'created_at',
                    '>=',
                    $today
                )->count(),

                'sales_amount' => Sale::where(
                    'created_at',
                    '>=',
                    $today
                )->sum('total_amount'),

                'payments' => Payment::where(
                    'created_at',
                    '>=',
                    $today
                )->sum('amount'),
            ],

            'order_status' => [
                'pending' => Order::where(
                    'status',
                    'pending'
                )->count(),

                'processing' => Order::where(
                    'status',
                    'processing'
                )->count(),

                'completed' => Order::where(
                    'status',
                    'completed'
                )->count(),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INVENTORY STAFF DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function inventoryStaffDashboard(): array
    {
        $lowStockQuery = Product::whereColumn(
            'stock_quantity',
            '<=',
            'reorder_level'
        )
            ->where(
                'stock_quantity',
                '>',
                0
            );


        return [
            'dashboard' => 'inventory_staff',

            'summary' => [
                'products' => Product::count(),

                'low_stock' => $lowStockQuery->count(),

                'out_of_stock' => Product::where(
                    'stock_quantity',
                    '<=',
                    0
                )->count(),

                'active' => Product::where(
                    'is_active',
                    true
                )->count(),
            ],

            'low_stock_items' => $lowStockQuery
                ->orderBy('stock_quantity')
                ->get([
                    'id',
                    'name',
                    'sku',
                    'stock_quantity',
                    'reorder_level',
                ]),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PROCUREMENT STAFF DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function procurementStaffDashboard(): array
    {
        $today = now()->startOfDay();


        $stockNeeds = Product::whereColumn(
            'stock_quantity',
            '<=',
            'reorder_level'
        )
            ->orderBy('stock_quantity')
            ->get([
                'id',
                'name',
                'sku',
                'stock_quantity',
                'reorder_level',
            ]);


        return [
            'dashboard' => 'procurement_staff',

            'summary' => [
                'suppliers' => Supplier::count(),
                'purchases' => Purchase::sum('total_amount'),
                'products' => Product::count(),
            ],

            'today' => [
                'count' => Purchase::where(
                    'created_at',
                    '>=',
                    $today
                )->count(),

                'amount' => Purchase::where(
                    'created_at',
                    '>=',
                    $today
                )->sum('total_amount'),
            ],

            'stock_needs' => $stockNeeds,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DELIVERY STAFF DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function deliveryStaffDashboard(): array
    {
        return [
            'dashboard' => 'delivery_staff',

            'summary' => [
                'deliveries' => Delivery::count(),

                'pending' => Delivery::where(
                    'status',
                    'pending'
                )->count(),

                'assigned' => Delivery::where(
                    'status',
                    'assigned'
                )->count(),

                'delivered' => Delivery::where(
                    'status',
                    'delivered'
                )->count(),
            ],

            'pending_deliveries' => Delivery::whereIn(
                'status',
                [
                    'pending',
                    'assigned',
                ]
            )
                ->latest()
                ->get(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BASIC / FALLBACK SUMMARY
    |--------------------------------------------------------------------------
    */

    private function basicSummary(): array
    {
        return [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_orders' => Order::count(),
            'total_sales' => Sale::count(),
        ];
    }
}