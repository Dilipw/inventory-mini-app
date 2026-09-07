<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockHistory>
 */
class StockHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 50),
            'type' => fake()->randomElement(['IN', 'OUT']),
            'remarks' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}