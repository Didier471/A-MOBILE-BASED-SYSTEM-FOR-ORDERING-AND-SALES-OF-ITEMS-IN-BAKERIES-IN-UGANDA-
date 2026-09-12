<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Wires up 'export reports' — this permission already existed in
 * RolePermissionSeeder but nothing used it. Same report queries as
 * ReportController, just rendered as a file instead of JSON.
 *
 * Routes (add to routes/api.php inside the existing 'export reports'
 * middleware group — see note in that file):
 *   GET /reports/sales/export?format=xlsx|pdf&from=&to=
 *   GET /reports/purchases/export?format=xlsx|pdf&from=&to=
 *   GET /reports/inventory/export?format=xlsx|pdf
 *   GET /reports/payments/export?format=xlsx|pdf&from=&to=
 */
class ReportExportController extends Controller
{
    public function sales(Request $request)
    {
        $query = Sale::query();
        $this->applyRange($query, $request);

        $rows = $query->select('sale_number', 'grand_total', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($sale) => [
                $sale->sale_number,
                number_format((float) $sale->grand_total, 2),
                $sale->created_at->format('Y-m-d H:i'),
            ]);

        return $this->respond(
            $request,
            'Sales Report',
            ['Sale #', 'Grand Total', 'Date'],
            $rows,
            $request->only('from', 'to')
        );
    }

    public function purchases(Request $request)
    {
        $query = Purchase::query();
        $this->applyRange($query, $request);

        $rows = $query->select('purchase_number', 'total_amount', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($purchase) => [
                $purchase->purchase_number,
                number_format((float) $purchase->total_amount, 2),
                $purchase->created_at->format('Y-m-d H:i'),
            ]);

        return $this->respond(
            $request,
            'Purchases Report',
            ['Purchase #', 'Total Amount', 'Date'],
            $rows,
            $request->only('from', 'to')
        );
    }

    public function inventory(Request $request)
    {
        $rows = Product::with('category')
            ->orderBy('stock_quantity')
            ->get()
            ->map(fn ($product) => [
                $product->sku,
                $product->name,
                $product->category->name ?? '—',
                $product->stock_quantity,
                $product->reorder_level,
                $product->stock_quantity <= 0 ? 'Out of stock' : ($product->stock_quantity <= $product->reorder_level ? 'Low' : 'OK'),
            ]);

        return $this->respond(
            $request,
            'Inventory Report',
            ['SKU', 'Product', 'Category', 'Stock', 'Reorder Level', 'Status'],
            $rows,
            []
        );
    }

    public function payments(Request $request)
    {
        $query = Payment::where('status', 'completed');
        $this->applyRange($query, $request);

        $rows = $query->select('payment_method', 'amount', 'transaction_reference', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($payment) => [
                $payment->payment_method,
                number_format((float) $payment->amount, 2),
                $payment->transaction_reference ?? '—',
                $payment->created_at->format('Y-m-d H:i'),
            ]);

        return $this->respond(
            $request,
            'Payments Report',
            ['Method', 'Amount', 'Reference', 'Date'],
            $rows,
            $request->only('from', 'to')
        );
    }

    private function applyRange($query, Request $request): void
    {
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }
    }

    private function respond(Request $request, string $title, array $headings, Collection $rows, array $period)
    {
        $format = $request->input('format', 'xlsx');
        $filename = str($title)->slug() . '-' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            return Pdf::loadView('reports.generic-pdf', [
                'title' => $title,
                'headings' => $headings,
                'rows' => $rows,
                'period' => $period,
            ])->download("{$filename}.pdf");
        }

        return Excel::download(new ReportExport($rows, $headings), "{$filename}.xlsx");
    }
}