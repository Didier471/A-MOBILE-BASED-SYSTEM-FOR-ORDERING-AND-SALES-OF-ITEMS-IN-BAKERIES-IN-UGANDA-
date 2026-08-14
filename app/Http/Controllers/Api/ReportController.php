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