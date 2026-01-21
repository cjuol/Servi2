<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $taxBase = fake()->numberBetween(10000, 100000); // Entre 100€ y 1000€
        $taxRate = 21; // IVA del 21%
        $taxRateQuantity = (int) ($taxBase * $taxRate / 100);

        return [
            'supplier_id' => Supplier::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'tax_base' => $taxBase,
            'tax_rate_quantity' => $taxRateQuantity,
        ];
    }
}
