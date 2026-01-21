<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'tax_base' => 0, // Se calculará con las líneas
            'tax_rate_quantity' => 0, // Se calculará con las líneas
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
