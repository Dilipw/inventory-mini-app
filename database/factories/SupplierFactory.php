<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('9#########'),
            'address' => fake()->address(),
            'gst_number' => fake()->unique()->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]'),
        ];
    }
}