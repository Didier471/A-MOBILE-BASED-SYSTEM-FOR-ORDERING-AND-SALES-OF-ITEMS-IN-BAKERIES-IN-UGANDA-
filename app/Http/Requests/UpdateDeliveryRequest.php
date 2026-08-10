<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|nullable|exists:customers,id',

            'delivery_address' => 'sometimes|required|string|max:500',

            'recipient_name' => 'sometimes|required|string|max:255',

            'recipient_phone' => 'sometimes|required|string|max:20',

            'delivery_fee' => 'sometimes|numeric|min:0',

            'status' => 'sometimes|required|in:pending,assigned,out_for_delivery,delivered,cancelled',

            'assigned_to' => 'sometimes|nullable|exists:users,id',

            'scheduled_at' => 'sometimes|nullable|date',

            'delivered_at' => 'sometimes|nullable|date',

            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}