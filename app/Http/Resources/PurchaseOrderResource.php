<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->supplier_name,
            ],
            'purchase_date' => $this->purchase_date,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'items' => PurchaseOrderItemResource::collection(
                $this->whenLoaded('items')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}