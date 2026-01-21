<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tiene_fillable_correcto(): void
    {
        $supplier = new Supplier();

        $expectedFillable = [
            'name',
            'contact_name',
            'email',
            'phone',
        ];

        $this->assertEquals($expectedFillable, $supplier->getFillable());
    }

    /** @test */
    public function no_tiene_casts_definidos(): void
    {
        $supplier = new Supplier();

        $casts = $supplier->getCasts();

        // Solo debería tener los casts por defecto de Eloquent (id, created_at, updated_at)
        $this->assertArrayNotHasKey('name', $casts);
        $this->assertArrayNotHasKey('email', $casts);
    }

    /** @test */
    public function relacion_products_esta_definida(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $supplier->products()
        );
    }

    /** @test */
    public function modelo_usa_uuid_trait(): void
    {
        $reflection = new \ReflectionClass(Supplier::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            $traits
        );
    }

    /** @test */
    public function modelo_usa_has_factory_trait(): void
    {
        $reflection = new \ReflectionClass(Supplier::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Factories\HasFactory',
            $traits
        );
    }

    /** @test */
    public function no_usa_soft_deletes(): void
    {
        $reflection = new \ReflectionClass(Supplier::class);
        $traits = $reflection->getTraitNames();

        $this->assertNotContains(
            'Illuminate\Database\Eloquent\SoftDeletes',
            $traits
        );
    }

    /** @test */
    public function puede_crear_instancia_con_factory(): void
    {
        $supplier = Supplier::factory()->make();

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertNotEmpty($supplier->name);
    }

    /** @test */
    public function factory_genera_datos_validos(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertNotNull($supplier->name);
        $this->assertIsString($supplier->name);
    }

    /** @test */
    public function factory_puede_generar_contact_name(): void
    {
        $supplier = Supplier::factory()->create();

        // El factory puede generar contact_name o puede ser null (opcional)
        if ($supplier->contact_name) {
            $this->assertIsString($supplier->contact_name);
        } else {
            $this->assertNull($supplier->contact_name);
        }
    }

    /** @test */
    public function factory_puede_generar_email(): void
    {
        $supplier = Supplier::factory()->create();

        // El factory puede generar email o puede ser null (opcional)
        if ($supplier->email) {
            $this->assertIsString($supplier->email);
            $this->assertStringContainsString('@', $supplier->email);
        } else {
            $this->assertNull($supplier->email);
        }
    }

    /** @test */
    public function factory_puede_generar_phone(): void
    {
        $supplier = Supplier::factory()->create();

        // El factory puede generar phone o puede ser null (opcional)
        if ($supplier->phone) {
            $this->assertIsString($supplier->phone);
        } else {
            $this->assertNull($supplier->phone);
        }
    }

    /** @test */
    public function timestamps_estan_habilitados(): void
    {
        $supplier = new Supplier();

        $this->assertTrue($supplier->usesTimestamps());
    }

    /** @test */
    public function tabla_es_suppliers(): void
    {
        $supplier = new Supplier();

        $this->assertEquals('suppliers', $supplier->getTable());
    }

    /** @test */
    public function relacion_products_retorna_coleccion(): void
    {
        $supplier = Supplier::factory()->create();
        
        Product::factory()->count(3)->create(['supplier_id' => $supplier->id]);

        $products = $supplier->products;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $products);
        $this->assertCount(3, $products);
    }

    /** @test */
    public function puede_acceder_a_atributos_fillable(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'contact_name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '123456789',
        ]);

        $this->assertEquals('Test Supplier', $supplier->name);
        $this->assertEquals('John Doe', $supplier->contact_name);
        $this->assertEquals('john@test.com', $supplier->email);
        $this->assertEquals('123456789', $supplier->phone);
    }

    /** @test */
    public function key_type_es_string_por_uuid(): void
    {
        $supplier = new Supplier();

        $this->assertEquals('string', $supplier->getKeyType());
    }

    /** @test */
    public function usa_uuids_correctamente(): void
    {
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();

        // Los IDs deben ser strings
        $this->assertIsString($supplier1->id);
        $this->assertIsString($supplier2->id);
        
        // Los IDs deben ser diferentes
        $this->assertNotEquals($supplier1->id, $supplier2->id);
    }
}
