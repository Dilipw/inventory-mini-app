<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_name' => fake()->words(3, true),
            'sku' => 'SKU-' . fake()->unique()->numerify('#####'),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'supplier_id' => Supplier::query()->inRandomOrder()->value('id'),
            'purchase_price' => fake()->randomFloat(2, 100, 5000),
            'selling_price' => fake()->randomFloat(2, 150, 7000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'minimum_stock' => fake()->numberBetween(5, 20),
            'description' => fake()->sentence(),
            'status' => true,
        ];
    }
}