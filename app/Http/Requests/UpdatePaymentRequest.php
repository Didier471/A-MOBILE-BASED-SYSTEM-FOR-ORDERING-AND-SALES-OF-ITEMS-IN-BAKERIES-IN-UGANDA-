<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentId = $this->route('payment')->id;

        return [
            'amount' => 'sometimes|required|numeric|min:0.01',

            'payment_method' => [
                'sometimes',
                'required',
                Rule::in([
                    'cash',
                    'mobile_money',
                    'card',
                    'bank_transfer'
                ]),
            ],

            'status' => [
                'sometimes',
                'required',
                Rule::in([
                    'pending',
                    'completed',
                    'failed',
                    'refunded'
                ]),
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
                'unique:payments,transaction_reference,' . $paymentId,
            ],

            'paid_at' => 'nullable|date',

            'remarks' => 'nullable|string|max:1000',
        ];
    }
}