<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($this->route('category')),
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
