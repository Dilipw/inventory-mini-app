<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('low_stock')) {
            $data['low_stock'] = filter_var(
                $this->input('low_stock'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        if ($this->has('status')) {
            $data['status'] = filter_var(
                $this->input('status'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        $this->merge($data);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],

            'supplier_id' => [
                'sometimes',
                'integer',
                'exists:suppliers,id',
            ],

            'low_stock' => [
                'sometimes',
                'boolean',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],

            'min_price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'sometimes',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'min_stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'max_stock' => [
                'sometimes',
                'integer',
                'min:0',
                'gte:min_stock',
            ],

            'sort_by' => [
                'sometimes',
                'string',
                Rule::in([
                    'product_name',
                    'sku',
                    'purchase_price',
                    'selling_price',
                    'stock_quantity',
                    'minimum_stock',
                    'created_at',
                ]),
            ],

            'sort_direction' => [
                'sometimes',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}