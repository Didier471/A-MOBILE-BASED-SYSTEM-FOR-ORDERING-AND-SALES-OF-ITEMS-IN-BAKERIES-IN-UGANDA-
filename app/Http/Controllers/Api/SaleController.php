<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index()
    {
        return response()->json(
            Sale::with(['customer', 'items.product'])
                ->latest()
                ->paginate(10)
        );
    }

    public function store(StoreSaleRequest $request)
    {
        DB::beginTransaction();

        try {

            $total = 0;

            $sale = Sale::create([
                'sale_number' => 'SAL-' . strtoupper(Str::random(8)),
                'customer_id' => $request->customer_id,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'total_amount' => 0,
                'grand_total' => 0,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {

                $product = Product::findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception(
                        "Insufficient stock for {$product->name}"
                    );
                }

                $subtotal = $product->selling_price * $item['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement(
                    'stock_quantity',
                    $item['quantity']
                );

                $total += $subtotal;
            }

            $grandTotal = $total
                - ($request->discount ?? 0)
                + ($request->tax ?? 0);

            $sale->update([
                'total_amount' => $total,
                'grand_total' => $grandTotal,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Sale completed successfully.',
                'data' => $sale->load('customer', 'items.product')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Sale $sale)
    {
        return response()->json(
            $sale->load(['customer', 'items.product'])
        );
    }

    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale->update($request->validated());

        return response()->json([
            'message' => 'Sale updated successfully.',
            'data' => $sale
        ]);
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return response()->json([
            'message' => 'Sale deleted successfully.'
        ]);
    }
}