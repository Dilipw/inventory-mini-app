<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Exceptions\PurchaseAlreadyCompletedException;


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
                fn(array $item) => $item['quantity'] * $item['price']
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

    public function completeOrder(
        int $purchaseOrderId,
        int $userId,
        StockService $stockService
    ): PurchaseOrder {
        return DB::transaction(function () use (
            $purchaseOrderId,
            $userId,
            $stockService
        ) {
            $purchaseOrder = PurchaseOrder::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchaseOrderId);

            if ($purchaseOrder->status === 'completed') {
                throw new PurchaseAlreadyCompletedException();
            }

            foreach ($purchaseOrder->items as $item) {
                $stockService->addStock(
                    $item->product_id,
                    $item->quantity,
                    "Purchase Order #{$purchaseOrder->id}",
                    $userId
                );
            }

            $purchaseOrder->update([
                'status' => 'completed',
            ]);

            return $purchaseOrder->fresh([
                'supplier',
                'items.product',
            ]);
        });
    }
}
