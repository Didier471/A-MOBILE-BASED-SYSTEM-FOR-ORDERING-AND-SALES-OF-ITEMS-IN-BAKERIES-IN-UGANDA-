<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',

            'status' => 'nullable|in:pending,confirmed,processing,ready,completed,cancelled',

            'discount' => 'nullable|numeric|min:0',

            'tax' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',

            'items.*.product_id' => 'required|exists:products,id',

            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}