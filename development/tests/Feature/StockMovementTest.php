<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function puede_crear_un_movimiento_de_stock(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $movementData = [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 50,
            'type' => StockMovement::TYPE_PURCHASE,
            'reason' => 'Compra inicial',
        ];

        $movement = StockMovement::create($movementData);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 50,
            'type' => StockMovement::TYPE_PURCHASE,
        ]);

        $this->assertEquals(50, $movement->quantity);
        $this->assertEquals(StockMovement::TYPE_PURCHASE, $movement->type);
    }

    /** @test */
    public function movimiento_pertenece_a_un_producto(): void
    {
        $product = Product::factory()->create(['name' => 'Producto Test']);
        $movement = StockMovement::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $movement->product);
        $this->assertEquals('Producto Test', $movement->product->name);
    }

    /** @test */
    public function movimiento_pertenece_a_un_usuario(): void
    {
        $user = User::factory()->create(['name' => 'Usuario Test']);
        $movement = StockMovement::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $movement->user);
        $this->assertEquals('Usuario Test', $movement->user->name);
    }

    /** @test */
    public function scope_by_type_filtra_correctamente(): void
    {
        StockMovement::factory()->purchase()->count(3)->create();
        StockMovement::factory()->sale()->count(2)->create();
        StockMovement::factory()->waste()->count(1)->create();

        $purchases = StockMovement::byType(StockMovement::TYPE_PURCHASE)->get();
        $sales = StockMovement::byType(StockMovement::TYPE_SALE)->get();

        $this->assertCount(3, $purchases);
        $this->assertCount(2, $sales);
        $this->assertTrue($purchases->every(fn ($m) => $m->type === StockMovement::TYPE_PURCHASE));
    }

    /** @test */
    public function scope_for_product_filtra_correctamente(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        StockMovement::factory()->count(5)->create(['product_id' => $product1->id]);
        StockMovement::factory()->count(3)->create(['product_id' => $product2->id]);

        $movementsProduct1 = StockMovement::forProduct($product1->id)->get();

        $this->assertCount(5, $movementsProduct1);
        $this->assertTrue($movementsProduct1->every(fn ($m) => $m->product_id === $product1->id));
    }

    /** @test */
    public function puede_crear_movimiento_de_compra(): void
    {
        $movement = StockMovement::factory()->purchase()->create();

        $this->assertEquals(StockMovement::TYPE_PURCHASE, $movement->type);
        $this->assertGreaterThan(0, $movement->quantity);
    }

    /** @test */
    public function puede_crear_movimiento_de_venta(): void
    {
        $movement = StockMovement::factory()->sale()->create();

        $this->assertEquals(StockMovement::TYPE_SALE, $movement->type);
        $this->assertLessThan(0, $movement->quantity);
    }

    /** @test */
    public function puede_crear_movimiento_de_merma(): void
    {
        $movement = StockMovement::factory()->waste()->create();

        $this->assertEquals(StockMovement::TYPE_WASTE, $movement->type);
        $this->assertLessThan(0, $movement->quantity);
    }

    /** @test */
    public function puede_crear_movimiento_de_ajuste(): void
    {
        $movement = StockMovement::factory()->adjustment()->create();

        $this->assertEquals(StockMovement::TYPE_ADJUSTMENT, $movement->type);
    }

    /** @test */
    public function quantity_se_guarda_como_entero(): void
    {
        $movement = StockMovement::factory()->create(['quantity' => 100]);

        $this->assertIsInt($movement->quantity);
        $this->assertEquals(100, $movement->quantity);
    }

    /** @test */
    public function puede_tener_cantidad_negativa(): void
    {
        $movement = StockMovement::factory()->create(['quantity' => -50]);

        $this->assertEquals(-50, $movement->quantity);
        $this->assertLessThan(0, $movement->quantity);
    }

    /** @test */
    public function reason_puede_ser_nulo(): void
    {
        $movement = StockMovement::factory()->create(['reason' => null]);

        $this->assertNull($movement->reason);
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'reason' => null,
        ]);
    }

    /** @test */
    public function product_id_es_obligatorio(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        StockMovement::create([
            'user_id' => User::factory()->create()->id,
            'quantity' => 10,
            'type' => StockMovement::TYPE_PURCHASE,
        ]);
    }

    /** @test */
    public function type_es_obligatorio(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        StockMovement::create([
            'product_id' => Product::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function usa_uuid_como_clave_primaria(): void
    {
        $movement = StockMovement::factory()->create();

        $this->assertIsString($movement->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $movement->id
        );
        $this->assertEquals(36, strlen($movement->id));
    }

    /** @test */
    public function puede_obtener_movimientos_con_producto_y_usuario(): void
    {
        $movement = StockMovement::factory()->create();

        $movementWithRelations = StockMovement::with(['product', 'user'])->find($movement->id);

        $this->assertTrue($movementWithRelations->relationLoaded('product'));
        $this->assertTrue($movementWithRelations->relationLoaded('user'));
    }

    /** @test */
    public function puede_filtrar_movimientos_recientes(): void
    {
        StockMovement::factory()->count(10)->create();

        $recentMovements = StockMovement::orderBy('created_at', 'desc')->limit(5)->get();

        $this->assertCount(5, $recentMovements);
    }

    /** @test */
    public function puede_contar_movimientos_por_tipo(): void
    {
        StockMovement::factory()->purchase()->count(5)->create();
        StockMovement::factory()->sale()->count(3)->create();

        $purchaseCount = StockMovement::where('type', StockMovement::TYPE_PURCHASE)->count();
        $saleCount = StockMovement::where('type', StockMovement::TYPE_SALE)->count();

        $this->assertEquals(5, $purchaseCount);
        $this->assertEquals(3, $saleCount);
    }

    /** @test */
    public function tipos_de_movimiento_son_constantes(): void
    {
        $this->assertEquals('sale', StockMovement::TYPE_SALE);
        $this->assertEquals('purchase', StockMovement::TYPE_PURCHASE);
        $this->assertEquals('adjustment', StockMovement::TYPE_ADJUSTMENT);
        $this->assertEquals('waste', StockMovement::TYPE_WASTE);
    }

    /** @test */
    public function puede_calcular_total_de_movimientos_por_producto(): void
    {
        $product = Product::factory()->create();

        StockMovement::factory()->create(['product_id' => $product->id, 'quantity' => 100]);
        StockMovement::factory()->create(['product_id' => $product->id, 'quantity' => -30]);
        StockMovement::factory()->create(['product_id' => $product->id, 'quantity' => 50]);

        $total = StockMovement::where('product_id', $product->id)->sum('quantity');

        $this->assertEquals(120, $total);
    }
}
