<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

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
                fn (array $item) => $item['quantity'] * $item['price']
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
}