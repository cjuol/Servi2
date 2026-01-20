<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            StockMovement::TYPE_SALE,
            StockMovement::TYPE_PURCHASE,
            StockMovement::TYPE_ADJUSTMENT,
            StockMovement::TYPE_WASTE,
        ];

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'quantity' => fake()->numberBetween(-50, 100),
            'type' => fake()->randomElement($types),
            'reason' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Movimiento de compra (positivo).
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_PURCHASE,
            'quantity' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Movimiento de venta (negativo).
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_SALE,
            'quantity' => fake()->numberBetween(-50, -1),
        ]);
    }

    /**
     * Movimiento de ajuste.
     */
    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_ADJUSTMENT,
        ]);
    }

    /**
     * Movimiento de merma (negativo).
     */
    public function waste(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_WASTE,
            'quantity' => fake()->numberBetween(-20, -1),
        ]);
    }
}
