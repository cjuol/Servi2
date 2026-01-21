<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryNote>
 */
class DeliveryNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => null, // Opcional
            'invoice_id' => null, // Opcional
            'date' => fake()->dateTimeBetween('-15 days', 'now'),
            'tax_base' => 0, // Se calculará con las líneas
            'tax_rate_quantity' => 0, // Se calculará con las líneas
            'stored' => false,
        ];
    }

    /**
     * Indica que el albarán viene de un presupuesto
     */
    public function fromBudget(?Budget $budget = null): static
    {
        return $this->state(fn (array $attributes) => [
            'budget_id' => $budget?->id ?? Budget::factory(),
        ]);
    }

    /**
     * Indica que el albarán está asignado a una factura
     */
    public function withInvoice(?Invoice $invoice = null): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice?->id ?? Invoice::factory(),
        ]);
    }

    /**
     * Indica que el albarán ya está almacenado
     */
    public function stored(): static
    {
        return $this->state(fn (array $attributes) => [
            'stored' => true,
        ]);
    }
}
