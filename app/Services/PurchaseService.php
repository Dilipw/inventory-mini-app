<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function createOrder(
        int $supplierId,
        string $purchaseDate,
        array $items
    ): PurchaseOrder {
        return DB::transaction(function () use (
            $supplierId,
            $purchaseDate,
            $items
        ) {
            $totalAmount = collect($items)->sum(
                fn (array $item) => $item['quantity'] * $item['price']
            );

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $supplierId,
                'purchase_date' => $purchaseDate,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            $purchaseOrder->items()->createMany($items);

            return $purchaseOrder->load([
                'supplier',
                'items.product',
            ]);
        });
    }
}