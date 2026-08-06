<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index()
    {
        return response()->json(
            Purchase::with(['supplier', 'items.product'])
                ->latest()
                ->paginate(10)
        );
    }

    public function store(StorePurchaseRequest $request)
    {
        DB::beginTransaction();

        try {

            $purchase = Purchase::create([
                'purchase_number' => 'PUR-' . strtoupper(Str::random(8)),
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($request->items as $item) {

                $subtotal = $item['quantity'] * $item['unit_cost'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::find($item['product_id']);

                $product->increment(
                    'stock_quantity',
                    $item['quantity']
                );

                $total += $subtotal;
            }

            $purchase->update([
                'total_amount' => $total
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase created successfully.',
                'data' => $purchase->load('items.product')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);

        }
    }

    public function show(Purchase $purchase)
    {
        return response()->json(
            $purchase->load(['supplier', 'items.product'])
        );
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $purchase->update($request->validated());

        return response()->json([
            'message' => 'Purchase updated successfully.',
            'data' => $purchase
        ]);
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();

        return response()->json([
            'message' => 'Purchase deleted successfully.'
        ]);
    }
}