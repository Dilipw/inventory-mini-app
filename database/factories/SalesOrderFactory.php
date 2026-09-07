<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'order_date' => fake()->date(),
            'total_amount' => fake()->randomFloat(2, 500, 50000),
            'status' => 'pending',
        ];
    }
}