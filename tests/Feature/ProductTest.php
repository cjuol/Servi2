<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un usuario para autenticación si es necesario
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function puede_crear_un_producto(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $productData = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Producto Test',
            'barcode' => '1234567890123',
            'sku' => 'SKU-001',
            'cost_price' => 1000,
            'sale_price' => 2000,
            'tax_rate' => 21,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'track_stock' => true,
        ];

        $product = Product::create($productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Producto Test',
            'barcode' => '1234567890123',
        ]);

        $this->assertEquals('Producto Test', $product->name);
        $this->assertEquals(1000, $product->cost_price);
    }

    /** @test */
    public function puede_actualizar_un_producto(): void
    {
        $product = Product::factory()->create([
            'name' => 'Producto Original',
        ]);

        $product->update([
            'name' => 'Producto Actualizado',
            'sale_price' => 3000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto Actualizado',
            'sale_price' => 3000,
        ]);
    }

    /** @test */
    public function puede_eliminar_un_producto_soft_delete(): void
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function puede_restaurar_un_producto_eliminado(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $product->restore();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function scope_active_devuelve_solo_productos_activos(): void
    {
        Product::factory()->active()->count(5)->create();
        Product::factory()->inactive()->count(3)->create();

        $activeProducts = Product::active()->get();

        $this->assertCount(5, $activeProducts);
        $this->assertTrue($activeProducts->every(fn ($prod) => $prod->is_active === true));
    }

    /** @test */
    public function scope_low_stock_devuelve_productos_con_stock_bajo(): void
    {
        // Producto con stock bajo
        Product::factory()->create([
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
            'track_stock' => true,
        ]);

        // Producto con stock normal
        Product::factory()->create([
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
            'track_stock' => true,
        ]);

        // Producto sin tracking
        Product::factory()->create([
            'stock_quantity' => 2,
            'low_stock_threshold' => 5,
            'track_stock' => false,
        ]);

        $lowStockProducts = Product::lowStock()->get();

        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals(3, $lowStockProducts->first()->stock_quantity);
    }

    /** @test */
    public function producto_pertenece_a_una_categoria(): void
    {
        $category = Category::factory()->create(['name' => 'Electrónica']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Electrónica', $product->category->name);
    }

    /** @test */
    public function producto_pertenece_a_un_proveedor(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Proveedor Test']);
        $product = Product::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $product->supplier);
        $this->assertEquals('Proveedor Test', $product->supplier->name);
    }

    /** @test */
    public function producto_puede_tener_multiples_movimientos_de_stock(): void
    {
        $product = Product::factory()->create();

        StockMovement::factory()->count(5)->create([
            'product_id' => $product->id,
        ]);

        $this->assertCount(5, $product->stockMovements);
        $this->assertInstanceOf(StockMovement::class, $product->stockMovements->first());
    }

    /** @test */
    public function el_barcode_debe_ser_unico(): void
    {
        Product::factory()->create([
            'barcode' => 'BARCODE-UNICO',
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Product::factory()->create([
            'barcode' => 'BARCODE-UNICO',
        ]);
    }

    /** @test */
    public function el_sku_debe_ser_unico(): void
    {
        Product::factory()->create([
            'sku' => 'SKU-UNICO',
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Product::factory()->create([
            'sku' => 'SKU-UNICO',
        ]);
    }

    /** @test */
    public function el_nombre_es_obligatorio(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::create([
            'category_id' => Category::factory()->create()->id,
            'cost_price' => 1000,
            'sale_price' => 2000,
        ]);
    }

    /** @test */
    public function precios_se_guardan_como_enteros(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 1550,
            'sale_price' => 2999,
        ]);

        $this->assertIsInt($product->cost_price);
        $this->assertIsInt($product->sale_price);
        $this->assertEquals(1550, $product->cost_price);
        $this->assertEquals(2999, $product->sale_price);
    }

    /** @test */
    public function accessor_cost_price_formatted_funciona(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 1550, // 15.50€
        ]);

        $this->assertEquals('15.50', $product->cost_price_formatted);
    }

    /** @test */
    public function accessor_sale_price_formatted_funciona(): void
    {
        $product = Product::factory()->create([
            'sale_price' => 2999, // 29.99€
        ]);

        $this->assertEquals('29.99', $product->sale_price_formatted);
    }

    /** @test */
    public function accessor_price_with_tax_calcula_correctamente(): void
    {
        $product = Product::factory()->create([
            'sale_price' => 10000, // 100€
            'tax_rate' => 21, // 21% IVA
        ]);

        // 100€ + 21% = 121€
        $this->assertEquals(121.0, $product->price_with_tax);
    }

    /** @test */
    public function campos_booleanos_funcionan_correctamente(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'track_stock' => false,
        ]);

        $this->assertIsBool($product->is_active);
        $this->assertIsBool($product->track_stock);
        $this->assertTrue($product->is_active);
        $this->assertFalse($product->track_stock);
    }

    /** @test */
    public function puede_obtener_productos_con_categoria_y_proveedor(): void
    {
        $product = Product::factory()->create();

        $productWithRelations = Product::with(['category', 'supplier'])->find($product->id);

        $this->assertTrue($productWithRelations->relationLoaded('category'));
        $this->assertTrue($productWithRelations->relationLoaded('supplier'));
    }

    /** @test */
    public function usa_uuid_como_clave_primaria(): void
    {
        $product = Product::factory()->create();

        $this->assertIsString($product->id);
        // UUID v7 (Laravel 11+)
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $product->id
        );
        $this->assertEquals(36, strlen($product->id));
    }

    /** @test */
    public function stock_quantity_se_actualiza_correctamente(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 100,
        ]);

        $product->increment('stock_quantity', 50);

        $this->assertEquals(150, $product->fresh()->stock_quantity);

        $product->decrement('stock_quantity', 30);

        $this->assertEquals(120, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function puede_filtrar_productos_por_categoria(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->count(3)->create(['category_id' => $category1->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $productsCategory1 = Product::where('category_id', $category1->id)->get();

        $this->assertCount(3, $productsCategory1);
    }

    /** @test */
    public function puede_contar_movimientos_de_stock_por_producto(): void
    {
        $product = Product::factory()->create();

        StockMovement::factory()->count(7)->create([
            'product_id' => $product->id,
        ]);

        $productWithCount = Product::withCount('stockMovements')->find($product->id);

        $this->assertEquals(7, $productWithCount->stock_movements_count);
    }

    /** @test */
    public function puede_buscar_productos_sin_acentos(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        // Crear productos con acentos
        Product::factory()->create([
            'name' => 'Café Colombiano',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Product::factory()->create([
            'name' => 'Té Verde',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Product::factory()->create([
            'name' => 'Azúcar Moreno',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        // Buscar "cafe" debe encontrar "Café Colombiano"
        if (config('database.default') === 'pgsql') {
            $results = Product::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%cafe%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Café Colombiano', $results->first()->name);
        }

        // Buscar "te" debe encontrar "Té Verde"
        if (config('database.default') === 'pgsql') {
            $results = Product::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%te%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Té Verde', $results->first()->name);
        }

        // Buscar "azucar" debe encontrar "Azúcar Moreno"
        if (config('database.default') === 'pgsql') {
            $results = Product::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%azucar%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Azúcar Moreno', $results->first()->name);
        }
    }

    /** @test */
    public function puede_buscar_productos_por_categoria_sin_acentos(): void
    {
        $category = Category::factory()->create(['name' => 'Bebidas Frías']);
        $otherCategory = Category::factory()->create(['name' => 'Postres']);
        $supplier = Supplier::factory()->create();

        Product::factory()->create([
            'name' => 'Producto 1',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Product::factory()->create([
            'name' => 'Producto 2',
            'category_id' => $otherCategory->id,
            'supplier_id' => $supplier->id,
        ]);

        // Buscar "bebidas frias" debe encontrar productos de la categoría "Bebidas Frías"
        if (config('database.default') === 'pgsql') {
            $results = Product::whereHas('category', function ($query) {
                $query->whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%bebidas frias%']);
            })->get();

            $this->assertCount(1, $results);
            $this->assertEquals('Producto 1', $results->first()->name);
        }
    }

    /** @test */
    public function puede_buscar_productos_por_proveedor_sin_acentos(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create(['name' => 'Distribución García']);
        $otherSupplier = Supplier::factory()->create(['name' => 'Proveedor López']);

        Product::factory()->create([
            'name' => 'Producto A',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Product::factory()->create([
            'name' => 'Producto B',
            'category_id' => $category->id,
            'supplier_id' => $otherSupplier->id,
        ]);

        // Buscar "garcia" debe encontrar productos del proveedor "Distribución García"
        if (config('database.default') === 'pgsql') {
            $results = Product::whereHas('supplier', function ($query) {
                $query->whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%garcia%']);
            })->get();

            $this->assertCount(1, $results);
            $this->assertEquals('Producto A', $results->first()->name);
        }
    }
}
