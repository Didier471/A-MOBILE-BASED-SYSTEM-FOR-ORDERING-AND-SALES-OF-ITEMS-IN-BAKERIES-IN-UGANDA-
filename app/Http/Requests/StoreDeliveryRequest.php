<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',

            'customer_id' => 'nullable|exists:customers,id',

            'delivery_address' => 'required|string|max:500',

            'recipient_name' => 'required|string|max:255',

            'recipient_phone' => 'required|string|max:20',

            'delivery_fee' => 'nullable|numeric|min:0',

            'status' => 'nullable|in:pending,assigned,out_for_delivery,delivered,cancelled',

            'assigned_to' => 'nullable|exists:users,id',

            'scheduled_at' => 'nullable|date|after_or_equal:now',

            'delivered_at' => 'nullable|date',

            'notes' => 'nullable|string|max:1000',
        ];
    }
}