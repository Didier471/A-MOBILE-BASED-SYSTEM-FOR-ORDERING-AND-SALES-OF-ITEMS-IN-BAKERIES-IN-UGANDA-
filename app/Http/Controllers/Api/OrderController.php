<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with([
                'customer',
                'items.product',
                'user'
            ])
            ->latest()
            ->paginate(10)
        );
    }

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {

            $data = $request->validated();

            $items = $data['items'];

            $totalAmount = 0;

            foreach ($items as &$item) {

                $product = Product::findOrFail($item['product_id']);

                $item['unit_price'] = $product->selling_price;

                $item['subtotal'] =
                    $product->selling_price * $item['quantity'];

                $totalAmount += $item['subtotal'];
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;

            $grandTotal =
                $totalAmount - $discount + $tax;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $order;
        });

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order->load([
                'customer',
                'items.product',
                'user'
            ])
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load([
                'customer',
                'items.product',
                'user'
            ])
        );
    }

    public function update(
        UpdateOrderRequest $request,
        Order $order
    ) {
        $order->update($request->validated());

        return response()->json([
            'message' => 'Order updated successfully.',
            'data' => $order->load([
                'customer',
                'items.product',
                'user'
            ])
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.'
        ]);
    }
}