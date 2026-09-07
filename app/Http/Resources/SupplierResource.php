<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_name' => $this->supplier_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gst_number' => $this->gst_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}