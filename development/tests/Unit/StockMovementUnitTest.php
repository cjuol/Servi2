<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tiene_fillable_correcto(): void
    {
        $movement = new StockMovement();

        $expectedFillable = [
            'product_id',
            'user_id',
            'quantity',
            'type',
            'reason',
        ];

        $this->assertEquals($expectedFillable, $movement->getFillable());
    }

    /** @test */
    public function tiene_casts_correcto(): void
    {
        $movement = new StockMovement();

        $casts = $movement->getCasts();

        $this->assertArrayHasKey('quantity', $casts);
        $this->assertEquals('integer', $casts['quantity']);
    }

    /** @test */
    public function tiene_constantes_de_tipo_definidas(): void
    {
        $this->assertTrue(defined(StockMovement::class . '::TYPE_SALE'));
        $this->assertTrue(defined(StockMovement::class . '::TYPE_PURCHASE'));
        $this->assertTrue(defined(StockMovement::class . '::TYPE_ADJUSTMENT'));
        $this->assertTrue(defined(StockMovement::class . '::TYPE_WASTE'));
    }

    /** @test */
    public function relacion_product_esta_definida(): void
    {
        $movement = StockMovement::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $movement->product()
        );
    }

    /** @test */
    public function relacion_user_esta_definida(): void
    {
        $movement = StockMovement::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $movement->user()
        );
    }

    /** @test */
    public function scope_by_type_esta_definido(): void
    {
        $movement = new StockMovement();

        $this->assertTrue(method_exists($movement, 'scopeByType'));
    }

    /** @test */
    public function scope_for_product_esta_definido(): void
    {
        $movement = new StockMovement();

        $this->assertTrue(method_exists($movement, 'scopeForProduct'));
    }

    /** @test */
    public function scope_by_type_filtra_correctamente(): void
    {
        $purchaseMovement = StockMovement::factory()->purchase()->create();
        $saleMovement = StockMovement::factory()->sale()->create();

        $result = StockMovement::byType(StockMovement::TYPE_PURCHASE)->get();

        $this->assertTrue($result->contains($purchaseMovement));
        $this->assertFalse($result->contains($saleMovement));
    }

    /** @test */
    public function scope_for_product_filtra_correctamente(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $movement1 = StockMovement::factory()->create(['product_id' => $product1->id]);
        $movement2 = StockMovement::factory()->create(['product_id' => $product2->id]);

        $result = StockMovement::forProduct($product1->id)->get();

        $this->assertTrue($result->contains($movement1));
        $this->assertFalse($result->contains($movement2));
    }

    /** @test */
    public function modelo_usa_uuid_trait(): void
    {
        $reflection = new \ReflectionClass(StockMovement::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            $traits
        );
    }

    /** @test */
    public function modelo_usa_has_factory_trait(): void
    {
        $reflection = new \ReflectionClass(StockMovement::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Factories\HasFactory',
            $traits
        );
    }

    /** @test */
    public function puede_crear_instancia_con_factory(): void
    {
        $movement = StockMovement::factory()->make();

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertNotNull($movement->quantity);
        $this->assertNotNull($movement->type);
    }

    /** @test */
    public function factory_genera_datos_validos(): void
    {
        $movement = StockMovement::factory()->create();

        $this->assertNotNull($movement->product_id);
        $this->assertNotNull($movement->user_id);
        $this->assertNotNull($movement->quantity);
        $this->assertNotNull($movement->type);
        $this->assertIsInt($movement->quantity);
        $this->assertIsString($movement->type);
    }

    /** @test */
    public function factory_state_purchase_funciona(): void
    {
        $movement = StockMovement::factory()->purchase()->create();

        $this->assertEquals(StockMovement::TYPE_PURCHASE, $movement->type);
        $this->assertGreaterThan(0, $movement->quantity);
    }

    /** @test */
    public function factory_state_sale_funciona(): void
    {
        $movement = StockMovement::factory()->sale()->create();

        $this->assertEquals(StockMovement::TYPE_SALE, $movement->type);
        $this->assertLessThan(0, $movement->quantity);
    }

    /** @test */
    public function factory_state_adjustment_funciona(): void
    {
        $movement = StockMovement::factory()->adjustment()->create();

        $this->assertEquals(StockMovement::TYPE_ADJUSTMENT, $movement->type);
    }

    /** @test */
    public function factory_state_waste_funciona(): void
    {
        $movement = StockMovement::factory()->waste()->create();

        $this->assertEquals(StockMovement::TYPE_WASTE, $movement->type);
        $this->assertLessThan(0, $movement->quantity);
    }

    /** @test */
    public function no_usa_soft_deletes(): void
    {
        $reflection = new \ReflectionClass(StockMovement::class);
        $traits = $reflection->getTraitNames();

        $this->assertNotContains(
            'Illuminate\Database\Eloquent\SoftDeletes',
            $traits
        );
    }

    /** @test */
    public function timestamps_estan_habilitados(): void
    {
        $movement = new StockMovement();

        $this->assertTrue($movement->usesTimestamps());
    }
}
