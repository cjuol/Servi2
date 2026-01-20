<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Mesa 1', 'Mesa 2', 'Mesa 3', 'Mesa 4', 'Mesa 5',
                'Terraza 1', 'Terraza 2', 'Terraza 3', 'Terraza 4', 'Terraza 5',
                'VIP 1', 'VIP 2', 'Barra 1', 'Barra 2',
            ]),
            'capacity' => fake()->randomElement([2, 4, 6, 8]),
            'is_available' => true,
        ];
    }

    /**
     * Indicate that the table is occupied.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
