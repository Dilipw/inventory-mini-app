<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->product_name,
                'sku' => $this->product->sku,
            ],
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => number_format(
                $this->quantity * $this->price,
                2,
                '.',
                ''
            ),
        ];
    }
}