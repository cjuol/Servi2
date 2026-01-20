<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tiene_fillable_correcto(): void
    {
        $product = new Product();

        $expectedFillable = [
            'category_id',
            'supplier_id',
            'name',
            'barcode',
            'sku',
            'description',
            'image_path',
            'cost_price',
            'sale_price',
            'tax_rate',
            'stock_quantity',
            'low_stock_threshold',
            'is_active',
            'track_stock',
        ];

        $this->assertEquals($expectedFillable, $product->getFillable());
    }

    /** @test */
    public function tiene_casts_correcto(): void
    {
        $product = new Product();

        $casts = $product->getCasts();

        $this->assertArrayHasKey('cost_price', $casts);
        $this->assertArrayHasKey('sale_price', $casts);
        $this->assertArrayHasKey('tax_rate', $casts);
        $this->assertArrayHasKey('stock_quantity', $casts);
        $this->assertArrayHasKey('low_stock_threshold', $casts);
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertArrayHasKey('track_stock', $casts);

        $this->assertEquals('integer', $casts['cost_price']);
        $this->assertEquals('integer', $casts['sale_price']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('boolean', $casts['track_stock']);
    }

    /** @test */
    public function usa_soft_deletes(): void
    {
        $product = Product::factory()->create();

        $this->assertNull($product->deleted_at);

        $product->delete();

        $this->assertNotNull($product->fresh()->deleted_at);
    }

    /** @test */
    public function relacion_category_esta_definida(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $product->category()
        );
    }

    /** @test */
    public function relacion_supplier_esta_definida(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $product->supplier()
        );
    }

    /** @test */
    public function relacion_stock_movements_esta_definida(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $product->stockMovements()
        );
    }

    /** @test */
    public function scope_active_filtra_correctamente(): void
    {
        $activeProduct = Product::factory()->active()->create();
        $inactiveProduct = Product::factory()->inactive()->create();

        $result = Product::active()->get();

        $this->assertTrue($result->contains($activeProduct));
        $this->assertFalse($result->contains($inactiveProduct));
    }

    /** @test */
    public function scope_low_stock_filtra_correctamente(): void
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 2,
            'low_stock_threshold' => 5,
            'track_stock' => true,
        ]);

        $normalStockProduct = Product::factory()->create([
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
            'track_stock' => true,
        ]);

        $result = Product::lowStock()->get();

        $this->assertTrue($result->contains($lowStockProduct));
        $this->assertFalse($result->contains($normalStockProduct));
    }

    /** @test */
    public function modelo_usa_uuid_trait(): void
    {
        $reflection = new \ReflectionClass(Product::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            $traits
        );
    }

    /** @test */
    public function modelo_usa_soft_deletes_trait(): void
    {
        $reflection = new \ReflectionClass(Product::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\SoftDeletes',
            $traits
        );
    }

    /** @test */
    public function modelo_usa_has_factory_trait(): void
    {
        $reflection = new \ReflectionClass(Product::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Factories\HasFactory',
            $traits
        );
    }

    /** @test */
    public function puede_crear_instancia_con_factory(): void
    {
        $product = Product::factory()->make();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotEmpty($product->name);
    }

    /** @test */
    public function factory_genera_datos_validos(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->name);
        $this->assertNotNull($product->cost_price);
        $this->assertNotNull($product->sale_price);
        $this->assertNotNull($product->tax_rate);
        $this->assertIsInt($product->cost_price);
        $this->assertIsInt($product->sale_price);
        $this->assertIsBool($product->is_active);
        $this->assertIsBool($product->track_stock);
    }

    /** @test */
    public function factory_state_active_funciona(): void
    {
        $product = Product::factory()->active()->create();

        $this->assertTrue($product->is_active);
    }

    /** @test */
    public function factory_state_inactive_funciona(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->assertFalse($product->is_active);
    }

    /** @test */
    public function factory_state_out_of_stock_funciona(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->assertEquals(0, $product->stock_quantity);
    }

    /** @test */
    public function factory_state_low_stock_funciona(): void
    {
        $product = Product::factory()->lowStock()->create();

        $this->assertLessThanOrEqual($product->low_stock_threshold, $product->stock_quantity);
        $this->assertGreaterThanOrEqual(1, $product->stock_quantity);
    }

    /** @test */
    public function accessor_cost_price_formatted_retorna_string(): void
    {
        $product = Product::factory()->create(['cost_price' => 1550]);

        $this->assertIsString($product->cost_price_formatted);
        $this->assertEquals('15.50', $product->cost_price_formatted);
    }

    /** @test */
    public function accessor_sale_price_formatted_retorna_string(): void
    {
        $product = Product::factory()->create(['sale_price' => 2999]);

        $this->assertIsString($product->sale_price_formatted);
        $this->assertEquals('29.99', $product->sale_price_formatted);
    }

    /** @test */
    public function accessor_price_with_tax_retorna_float(): void
    {
        $product = Product::factory()->create([
            'sale_price' => 10000,
            'tax_rate' => 21,
        ]);

        $this->assertIsFloat($product->price_with_tax);
        $this->assertEquals(121.0, $product->price_with_tax);
    }

    /** @test */
    public function precios_con_cero_funcionan_correctamente(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 0,
            'sale_price' => 0,
        ]);

        $this->assertEquals('0.00', $product->cost_price_formatted);
        $this->assertEquals('0.00', $product->sale_price_formatted);
        $this->assertEquals(0.0, $product->price_with_tax);
    }
}
