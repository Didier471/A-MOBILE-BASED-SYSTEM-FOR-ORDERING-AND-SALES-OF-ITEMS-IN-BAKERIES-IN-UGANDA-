<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products')->ignore($this->route('product')),
            ],
            'barcode' => 'nullable|string|max:100',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}