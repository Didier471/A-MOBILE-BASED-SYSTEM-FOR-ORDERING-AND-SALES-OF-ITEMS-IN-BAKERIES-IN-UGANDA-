<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display inventory history.
     */
    public function index()
    {
        $transactions = InventoryTransaction::with(['product', 'user'])
            ->latest()
            ->paginate(10);

        return response()->json($transactions);
    }

    /**
     * Store an inventory transaction.
     */
    public function store(StoreInventoryRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'stock_in') {
            $product->stock_quantity += $request->quantity;
        }

        if ($request->type === 'stock_out') {

            if ($product->stock_quantity < $request->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock.'
                ], 422);
            }

            $product->stock_quantity -= $request->quantity;
        }

        if ($request->type === 'adjustment') {
            $product->stock_quantity = $request->quantity;
        }

        $product->save();

        $transaction = InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Inventory updated successfully.',
            'data' => $transaction->load(['product', 'user'])
        ], 201);
    }

    /**
     * Show one inventory transaction.
     */
    public function show(InventoryTransaction $inventory)
    {
        return response()->json(
            $inventory->load(['product', 'user'])
        );
    }

    public function update(Request $request, InventoryTransaction $inventory)
    {
        return response()->json([
            'message' => 'Updating inventory records is not allowed.'
        ], 405);
    }

    public function destroy(InventoryTransaction $inventory)
    {
        return response()->json([
            'message' => 'Deleting inventory records is not allowed.'
        ], 405);
    }
}