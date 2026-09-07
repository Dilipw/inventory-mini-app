<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use App\Exceptions\SalesOrderAlreadyCompletedException;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockHistory;


class SalesService
{
    public function createOrder(
        string $customerName,
        string $orderDate,
        array $items
    ): SalesOrder {
        return DB::transaction(function () use (
            $customerName,
            $orderDate,
            $items
        ) {
            $totalAmount = collect($items)->sum(
                fn(array $item) => $item['quantity'] * $item['price']
            );

            $salesOrder = SalesOrder::create([
                'customer_name' => $customerName,
                'order_date' => $orderDate,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            $salesOrder->items()->createMany($items);

            return $salesOrder->load([
                'items.product',
            ]);
        });
    }

    public function updateOrder(
        SalesOrder $salesOrder,
        ?string $customerName,
        ?string $orderDate,
        ?array $items
    ): SalesOrder {
        return DB::transaction(function () use (
            $salesOrder,
            $customerName,
            $orderDate,
            $items
        ) {
            $salesOrder->refresh();

            if ($salesOrder->status !== 'pending') {
                throw new SalesOrderAlreadyCompletedException();
            }

            $data = [];

            if ($customerName !== null) {
                $data['customer_name'] = $customerName;
            }

            if ($orderDate !== null) {
                $data['order_date'] = $orderDate;
            }

            if ($items !== null) {
                $data['total_amount'] = collect($items)->sum(
                    fn(array $item) => $item['quantity'] * $item['price']
                );
            }

            $salesOrder->update($data);

            if ($items !== null) {
                $salesOrder->items()->delete();
                $salesOrder->items()->createMany($items);
            }

            return $salesOrder->fresh([
                'items.product',
            ]);
        });
    }

    public function completeOrder(
        SalesOrder $salesOrder,
        int $userId
    ): SalesOrder {
        return DB::transaction(function () use (
            $salesOrder,
            $userId
        ) {
            $salesOrder->refresh();

            if ($salesOrder->status !== 'pending') {
                throw new SalesOrderAlreadyCompletedException();
            }

            $salesOrder->load('items');

            $requiredQuantities = collect($salesOrder->items)
                ->groupBy('product_id')
                ->map(
                    fn($items) => $items->sum('quantity')
                );

            $products = Product::query()
                ->whereIn('id', $requiredQuantities->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($requiredQuantities as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || $product->stock_quantity < $quantity) {
                    throw new InsufficientStockException();
                }
            }

            foreach ($requiredQuantities as $productId => $quantity) {
                $products->get($productId)->decrement(
                    'stock_quantity',
                    $quantity
                );

                StockHistory::create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'type' => 'OUT',
                    'remarks' => "Sales Order #{$salesOrder->id}",
                    'created_by' => $userId,
                ]);
            }

            $salesOrder->update([
                'status' => 'completed',
            ]);

            return $salesOrder->fresh([
                'items.product',
            ]);
        });
    }
}
