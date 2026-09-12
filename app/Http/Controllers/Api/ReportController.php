<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * General business report.
     */
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $salesQuery = Sale::query();
        $paymentsQuery = Payment::query();
        $purchasesQuery = Purchase::query();

        if ($from) {
            $salesQuery->whereDate('created_at', '>=', $from);
            $paymentsQuery->whereDate('created_at', '>=', $from);
            $purchasesQuery->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $salesQuery->whereDate('created_at', '<=', $to);
            $paymentsQuery->whereDate('created_at', '<=', $to);
            $purchasesQuery->whereDate('created_at', '<=', $to);
        }

        $sales = $salesQuery->sum('grand_total');

        $payments = $paymentsQuery
            ->where('status', 'completed')
            ->sum('amount');

        $purchases = $purchasesQuery->sum('total_amount');

        $orders = Order::query();

        if ($from) {
            $orders->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $orders->whereDate('created_at', '<=', $to);
        }

        $monthlySalesQuery = Sale::query();
        $monthlyPurchasesQuery = Purchase::query();
        $monthlyOrdersQuery = Order::query();
        $monthlyPaymentsQuery = Payment::where('status', 'completed');

        if ($from) {
            $monthlySalesQuery->whereDate('created_at', '>=', $from);
            $monthlyPurchasesQuery->whereDate('created_at', '>=', $from);
            $monthlyOrdersQuery->whereDate('created_at', '>=', $from);
            $monthlyPaymentsQuery->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $monthlySalesQuery->whereDate('created_at', '<=', $to);
            $monthlyPurchasesQuery->whereDate('created_at', '<=', $to);
            $monthlyOrdersQuery->whereDate('created_at', '<=', $to);
            $monthlyPaymentsQuery->whereDate('created_at', '<=', $to);
        }

        $monthlySales = $monthlySalesQuery
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(grand_total) as total'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyPurchases = $monthlyPurchasesQuery
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyOrders = $monthlyOrdersQuery
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyPayments = $monthlyPaymentsQuery
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = collect($monthlySales->keys())
            ->merge($monthlyPurchases->keys())
            ->merge($monthlyOrders->keys())
            ->merge($monthlyPayments->keys())
            ->unique()
            ->sort()
            ->values();

        $monthlyPerformance = $months->map(function ($month, $index) use ($monthlySales, $monthlyPurchases, $monthlyOrders, $monthlyPayments) {
            $salesTotal = (float) ($monthlySales->get($month)->total ?? 0);
            $purchaseTotal = (float) ($monthlyPurchases->get($month)->total ?? 0);
            $orderTotal = (int) ($monthlyOrders->get($month)->total ?? 0);
            $paymentTotal = (float) ($monthlyPayments->get($month)->total ?? 0);

            $previousMonth = $index > 0 ? $months[$index - 1] : null;
            $previousSales = $previousMonth ? (float) ($monthlySales->get($previousMonth)->total ?? 0) : null;
            $change = $previousSales !== null && $previousSales > 0
                ? (($salesTotal - $previousSales) / $previousSales) * 100
                : null;

            if ($change === null) {
                $description = $salesTotal > 0
                    ? 'Sales were recorded this month. This provides the starting point for future monthly comparisons.'
                    : 'No sales were recorded during this month.';
                $performance = $salesTotal > 0 ? 'Starting performance' : 'No sales';
            } elseif ($change > 0) {
                $description = 'Sales increased compared with the previous month, indicating stronger revenue performance.';
                $performance = 'Improving';
            } elseif ($change < 0) {
                $description = 'Sales decreased compared with the previous month. This month may need closer review of orders and sales activity.';
                $performance = 'Needs attention';
            } else {
                $description = 'Sales remained at the same level as the previous month, showing stable revenue performance.';
                $performance = 'Stable';
            }

            return [
                'month' => $month,
                'sales' => round($salesTotal, 2),
                'purchases' => round($purchaseTotal, 2),
                'orders' => $orderTotal,
                'payments' => round($paymentTotal, 2),
                'sales_change_percent' => $change !== null ? round($change, 1) : null,
                'performance' => $performance,
                'description' => $description,
            ];
        });

        $paymentMethods = $paymentsQuery
            ->select('payment_method', DB::raw('COUNT(*) as number_of_payments'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'message' => 'Report generated successfully.',

            'period' => [
                'from' => $from,
                'to' => $to,
            ],

            'summary' => [
                'total_sales' => $sales,
                'total_payments' => $payments,
                'total_purchases' => $purchases,
                'total_orders' => $orders->count(),
            ],
            'monthly_performance' => $monthlyPerformance,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Sales report.
     */
    public function sales(Request $request)
    {
        $query = Sale::query();

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as number_of_sales'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'message' => 'Sales report generated successfully.',
            'data' => $sales,
        ]);
    }

    /**
     * Purchase report.
     */
    public function purchases(Request $request)
    {
        $query = Purchase::query();

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $purchases = $query
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as number_of_purchases'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'message' => 'Purchase report generated successfully.',
            'data' => $purchases,
        ]);
    }

    /**
     * Inventory report.
     */
    public function inventory()
    {
        $products = Product::with('category')
            ->orderBy('stock_quantity')
            ->get();

        $lowStock = Product::whereColumn(
            'stock_quantity',
            '<=',
            'reorder_level'
        )->count();

        $outOfStock = Product::where(
            'stock_quantity',
            '<=',
            0
        )->count();

        return response()->json([
            'message' => 'Inventory report generated successfully.',

            'summary' => [
                'total_products' => Product::count(),
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ],

            'products' => $products,
        ]);
    }

    /**
     * Payment report.
     */
    public function payments(Request $request)
    {
        $query = Payment::where('status', 'completed');

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as number_of_payments'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'message' => 'Payment report generated successfully.',
            'data' => $payments,
        ]);
    }

    /**
     * Order report.
     */
    public function orders(Request $request)
    {
        $query = Order::query();

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->get();

        return response()->json([
            'message' => 'Order report generated successfully.',
            'data' => $orders,
        ]);
    }

    /**
     * Delivery report.
     */
    public function deliveries(Request $request)
    {
        $query = Delivery::query();

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $deliveries = $query
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->get();

        return response()->json([
            'message' => 'Delivery report generated successfully.',
            'data' => $deliveries,
        ]);
    }
}