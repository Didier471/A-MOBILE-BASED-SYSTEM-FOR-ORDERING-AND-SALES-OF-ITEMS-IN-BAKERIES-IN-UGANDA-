<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;

class DeliveryController extends Controller
{
    public function index()
    {
        return response()->json(
            Delivery::with([
                'sale',
                'customer',
                'assignedUser',
                'user'
            ])
            ->latest()
            ->paginate(10)
        );
    }

    public function store(StoreDeliveryRequest $request)
    {
        $delivery = Delivery::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Delivery created successfully.',
            'data' => $delivery->load([
                'sale',
                'customer',
                'assignedUser',
                'user'
            ])
        ], 201);
    }

    public function show(Delivery $delivery)
    {
        return response()->json(
            $delivery->load([
                'sale',
                'customer',
                'assignedUser',
                'user'
            ])
        );
    }

    public function update(
        UpdateDeliveryRequest $request,
        Delivery $delivery
    ) {
        $delivery->update($request->validated());

        return response()->json([
            'message' => 'Delivery updated successfully.',
            'data' => $delivery->load([
                'sale',
                'customer',
                'assignedUser',
                'user'
            ])
        ]);
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return response()->json([
            'message' => 'Delivery deleted successfully.'
        ]);
    }
}