<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',

            'amount' => 'required|numeric|min:0.01',

            'payment_method' => 'required|in:cash,mobile_money,card,bank_transfer',

            'status' => 'nullable|in:pending,completed,failed,refunded',

            'transaction_reference' => 'nullable|string|max:255|unique:payments,transaction_reference',

            'paid_at' => 'nullable|date',

            'remarks' => 'nullable|string|max:1000',
        ];
    }
}