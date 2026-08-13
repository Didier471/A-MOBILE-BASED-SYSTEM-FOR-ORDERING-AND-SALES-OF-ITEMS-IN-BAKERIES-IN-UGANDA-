<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|nullable|exists:customers,id',

            'status' => 'sometimes|required|in:pending,confirmed,processing,ready,completed,cancelled',

            'discount' => 'sometimes|numeric|min:0',

            'tax' => 'sometimes|numeric|min:0',

            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}