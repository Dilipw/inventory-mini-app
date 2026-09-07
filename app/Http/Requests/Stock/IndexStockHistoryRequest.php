<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStockHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'sometimes',
                'integer',
                'exists:products,id',
            ],
            'type' => [
                'sometimes',
                'string',
                Rule::in(['IN', 'OUT']),
            ],
            'sort_direction' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
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