<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'name' => fake()->words(3, true),
            'barcode' => fake()->unique()->ean13(),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'description' => fake()->optional()->sentence(),
            'image_path' => null,
            'cost_price' => fake()->numberBetween(100, 5000), // Centavos
            'sale_price' => fake()->numberBetween(200, 10000), // Centavos
            'tax_rate' => fake()->randomElement([10, 21]), // IVA común en España
            'stock_quantity' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'is_active' => fake()->boolean(90), // 90% activos
            'track_stock' => fake()->boolean(95), // 95% trackean stock
        ];
    }

    /**
     * Indica que el producto está activo.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indica que el producto está inactivo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Producto sin stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Producto con stock bajo.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(1, $attributes['low_stock_threshold'] ?? 5),
        ]);
    }
}
