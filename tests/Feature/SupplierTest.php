<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function puede_crear_un_proveedor(): void
    {
        $supplierData = [
            'name' => 'Proveedor Test',
            'contact_name' => 'Juan Pérez',
            'email' => 'juan@proveedor.com',
            'phone' => '123456789',
        ];

        $supplier = Supplier::create($supplierData);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Proveedor Test',
            'email' => 'juan@proveedor.com',
        ]);

        $this->assertEquals('Proveedor Test', $supplier->name);
        $this->assertEquals('Juan Pérez', $supplier->contact_name);
    }

    /** @test */
    public function puede_actualizar_un_proveedor(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Proveedor Original',
        ]);

        $supplier->update([
            'name' => 'Proveedor Actualizado',
            'email' => 'nuevo@email.com',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Proveedor Actualizado',
            'email' => 'nuevo@email.com',
        ]);
    }

    /** @test */
    public function puede_eliminar_un_proveedor(): void
    {
        $supplier = Supplier::factory()->create();

        $supplier->delete();

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    /** @test */
    public function proveedor_puede_tener_multiples_productos(): void
    {
        $supplier = Supplier::factory()->create();

        Product::factory()->count(5)->create([
            'supplier_id' => $supplier->id,
        ]);

        $this->assertCount(5, $supplier->products);
        $this->assertInstanceOf(Product::class, $supplier->products->first());
    }

    /** @test */
    public function el_nombre_es_obligatorio(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Supplier::create([
            'contact_name' => 'Juan',
            'email' => 'juan@test.com',
        ]);
    }

    /** @test */
    public function contact_name_puede_ser_nulo(): void
    {
        $supplier = Supplier::factory()->create(['contact_name' => null]);

        $this->assertNull($supplier->contact_name);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'contact_name' => null,
        ]);
    }

    /** @test */
    public function email_puede_ser_nulo(): void
    {
        $supplier = Supplier::factory()->create(['email' => null]);

        $this->assertNull($supplier->email);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'email' => null,
        ]);
    }

    /** @test */
    public function phone_puede_ser_nulo(): void
    {
        $supplier = Supplier::factory()->create(['phone' => null]);

        $this->assertNull($supplier->phone);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'phone' => null,
        ]);
    }

    /** @test */
    public function usa_uuid_como_clave_primaria(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertIsString($supplier->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $supplier->id
        );
        $this->assertEquals(36, strlen($supplier->id));
    }

    /** @test */
    public function puede_obtener_proveedores_con_productos(): void
    {
        $supplier = Supplier::factory()->create();
        
        Product::factory()->count(3)->create([
            'supplier_id' => $supplier->id,
        ]);

        $supplierWithProducts = Supplier::with('products')->find($supplier->id);

        $this->assertTrue($supplierWithProducts->relationLoaded('products'));
        $this->assertCount(3, $supplierWithProducts->products);
    }

    /** @test */
    public function puede_contar_productos_por_proveedor(): void
    {
        $supplier = Supplier::factory()->create();

        Product::factory()->count(7)->create([
            'supplier_id' => $supplier->id,
        ]);

        $supplierWithCount = Supplier::withCount('products')->find($supplier->id);

        $this->assertEquals(7, $supplierWithCount->products_count);
    }

    /** @test */
    public function puede_buscar_proveedores_por_nombre(): void
    {
        Supplier::factory()->create(['name' => 'Proveedor ABC']);
        Supplier::factory()->create(['name' => 'Proveedor XYZ']);
        Supplier::factory()->create(['name' => 'Distribuidor DEF']);

        $results = Supplier::where('name', 'LIKE', '%Proveedor%')->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function puede_buscar_proveedores_por_email(): void
    {
        Supplier::factory()->create(['email' => 'contacto@abc.com']);
        $supplier = Supplier::factory()->create(['email' => 'info@xyz.com']);

        $result = Supplier::where('email', 'info@xyz.com')->first();

        $this->assertEquals($supplier->id, $result->id);
    }

    /** @test */
    public function puede_listar_proveedores_ordenados_alfabeticamente(): void
    {
        Supplier::factory()->create(['name' => 'Zebra Supplies']);
        Supplier::factory()->create(['name' => 'Alpha Distributors']);
        Supplier::factory()->create(['name' => 'Beta Products']);

        $suppliers = Supplier::orderBy('name', 'asc')->get();

        $this->assertEquals('Alpha Distributors', $suppliers->first()->name);
        $this->assertEquals('Zebra Supplies', $suppliers->last()->name);
    }

    /** @test */
    public function proveedor_sin_productos_tiene_coleccion_vacia(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertCount(0, $supplier->products);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $supplier->products);
    }

    /** @test */
    public function puede_obtener_productos_activos_de_un_proveedor(): void
    {
        $supplier = Supplier::factory()->create();

        Product::factory()->active()->count(3)->create(['supplier_id' => $supplier->id]);
        Product::factory()->inactive()->count(2)->create(['supplier_id' => $supplier->id]);

        $activeProducts = $supplier->products()->where('is_active', true)->get();

        $this->assertCount(3, $activeProducts);
    }

    /** @test */
    public function timestamps_se_actualizan_correctamente(): void
    {
        $supplier = Supplier::factory()->create();
        
        $createdAt = $supplier->created_at;
        
        sleep(1);
        
        $supplier->update(['name' => 'Nuevo Nombre']);
        
        $this->assertEquals($createdAt->timestamp, $supplier->created_at->timestamp);
        $this->assertGreaterThan($createdAt->timestamp, $supplier->updated_at->timestamp);
    }

    /** @test */
    public function puede_buscar_proveedores_por_nombre_sin_acentos(): void
    {
        Supplier::factory()->create(['name' => 'Distribución García']);
        Supplier::factory()->create(['name' => 'Almacén López']);
        Supplier::factory()->create(['name' => 'Carnicería José']);

        // Buscar "garcia" debe encontrar "Distribución García"
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%garcia%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Distribución García', $results->first()->name);
        }

        // Buscar "lopez" debe encontrar "Almacén López"
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%lopez%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Almacén López', $results->first()->name);
        }

        // Buscar "jose" debe encontrar "Carnicería José"
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%jose%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Carnicería José', $results->first()->name);
        }
    }

    /** @test */
    public function puede_buscar_proveedores_por_persona_contacto_sin_acentos(): void
    {
        Supplier::factory()->create([
            'name' => 'Proveedor A',
            'contact_name' => 'José María Pérez',
        ]);

        Supplier::factory()->create([
            'name' => 'Proveedor B',
            'contact_name' => 'María González',
        ]);

        // Buscar "jose maria" debe encontrar el proveedor con contacto "José María Pérez"
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(contact_name::text)) LIKE unaccent(LOWER(?))", ['%jose maria%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('José María Pérez', $results->first()->contact_name);
        }

        // Buscar "maria" debe encontrar ambos proveedores
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(contact_name::text)) LIKE unaccent(LOWER(?))", ['%maria%'])->get();
            $this->assertCount(2, $results);
        }
    }

    /** @test */
    public function puede_buscar_proveedores_por_email_sin_acentos(): void
    {
        Supplier::factory()->create([
            'name' => 'Proveedor 1',
            'email' => 'josé@proveedor.com',
        ]);

        Supplier::factory()->create([
            'name' => 'Proveedor 2',
            'email' => 'maria@distribucion.com',
        ]);

        // Buscar "jose@proveedor" debe encontrar el email con acento
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(email::text)) LIKE unaccent(LOWER(?))", ['%jose@proveedor%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('josé@proveedor.com', $results->first()->email);
        }
    }

    /** @test */
    public function busqueda_sin_acentos_es_case_insensitive_en_proveedores(): void
    {
        Supplier::factory()->create(['name' => 'GARCÍA']);
        Supplier::factory()->create(['name' => 'garcía']);
        Supplier::factory()->create(['name' => 'García']);

        // Buscar "garcia" en cualquier variación de mayúsculas/minúsculas
        if (config('database.default') === 'pgsql') {
            $results = Supplier::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%garcia%'])->get();
            $this->assertCount(3, $results);
        }
    }
}
