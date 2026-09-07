<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class StockService
{
    public function addStock(
        int $productId,
        int $quantity,
        ?string $remarks,
        int $userId
    ): Product {
        return DB::transaction(function () use (
            $productId,
            $quantity,
            $remarks,
            $userId
        ) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            $product->increment('stock_quantity', $quantity);

            StockHistory::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => 'IN',
                'remarks' => $remarks,
                'created_by' => $userId,
            ]);

            return $product->fresh([
                'category',
                'supplier',
            ]);
        });
    }

    public function reduceStock(
        int $productId,
        int $quantity,
        ?string $remarks,
        int $userId
    ): Product {
        return DB::transaction(function () use (
            $productId,
            $quantity,
            $remarks,
            $userId
        ) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            if ($product->stock_quantity < $quantity) {
                throw new InsufficientStockException();
            }

            $product->decrement('stock_quantity', $quantity);

            StockHistory::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => 'OUT',
                'remarks' => $remarks,
                'created_by' => $userId,
            ]);

            return $product->fresh([
                'category',
                'supplier',
            ]);
        });
    }
}
