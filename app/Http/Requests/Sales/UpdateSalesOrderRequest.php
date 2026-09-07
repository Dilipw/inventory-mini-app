<?php

namespace App\Http\Requests\Sales;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'order_date' => [
                'sometimes',
                'date',
            ],
            'items' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}