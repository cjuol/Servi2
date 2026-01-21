<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un usuario para autenticación si es necesario
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function puede_crear_una_categoria(): void
    {
        $categoryData = [
            'name' => 'Electrónica',
            'slug' => 'electronica',
            'color' => '#FF5733',
            'is_active' => true,
        ];

        $category = Category::create($categoryData);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electrónica',
            'slug' => 'electronica',
        ]);

        $this->assertEquals('Electrónica', $category->name);
        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function puede_actualizar_una_categoria(): void
    {
        $category = Category::factory()->create([
            'name' => 'Categoría Original',
        ]);

        $category->update([
            'name' => 'Categoría Actualizada',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Categoría Actualizada',
        ]);
    }

    /** @test */
    public function puede_eliminar_una_categoria_soft_delete(): void
    {
        $category = Category::factory()->create();

        $category->delete();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);

        // Verificar que aún existe con trashed
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function puede_restaurar_una_categoria_eliminada(): void
    {
        $category = Category::factory()->create();
        $category->delete();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);

        $category->restore();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function scope_active_devuelve_solo_categorias_activas(): void
    {
        Category::factory()->active()->count(3)->create();
        Category::factory()->inactive()->count(2)->create();

        $activeCategories = Category::active()->get();

        $this->assertCount(3, $activeCategories);
        $this->assertTrue($activeCategories->every(fn ($cat) => $cat->is_active === true));
    }

    /** @test */
    public function una_categoria_puede_tener_multiples_productos(): void
    {
        $category = Category::factory()->create();

        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id,
        ]);

        $this->assertCount(5, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    /** @test */
    public function el_slug_debe_ser_unico(): void
    {
        Category::factory()->create([
            'slug' => 'categoria-unica',
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Category::factory()->create([
            'slug' => 'categoria-unica',
        ]);
    }

    /** @test */
    public function el_nombre_es_obligatorio(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::create([
            'slug' => 'test-slug',
            'color' => '#000000',
        ]);
    }

    /** @test */
    public function is_active_es_booleano(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $this->assertIsBool($category->is_active);
        $this->assertTrue($category->is_active);

        $category->update(['is_active' => false]);
        $category->refresh();

        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function puede_contar_productos_por_categoria(): void
    {
        $category = Category::factory()->create();

        Product::factory()->count(10)->create([
            'category_id' => $category->id,
        ]);

        $categoryWithCount = Category::withCount('products')->find($category->id);

        $this->assertEquals(10, $categoryWithCount->products_count);
    }

    /** @test */
    public function el_color_se_guarda_correctamente(): void
    {
        $category = Category::factory()->create([
            'color' => '#FF5733',
        ]);

        $this->assertEquals('#FF5733', $category->color);
    }

    /** @test */
    public function puede_obtener_categorias_con_sus_productos(): void
    {
        $category = Category::factory()->create();
        
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $categoryWithProducts = Category::with('products')->find($category->id);

        $this->assertTrue($categoryWithProducts->relationLoaded('products'));
        $this->assertCount(3, $categoryWithProducts->products);
    }

    /** @test */
    public function usa_uuid_como_clave_primaria(): void
    {
        $category = Category::factory()->create();

        $this->assertIsString($category->id);
        // UUID v7 (Laravel 11+) tiene un formato diferente a UUID v4
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $category->id
        );
        $this->assertEquals(36, strlen($category->id)); // UUID siempre tiene 36 caracteres
    }

    /** @test */
    public function puede_buscar_categorias_sin_acentos(): void
    {
        // Crear categorías con acentos
        Category::factory()->create(['name' => 'Bebidas Alcohólicas']);
        Category::factory()->create(['name' => 'Café y Té']);
        Category::factory()->create(['name' => 'Panadería']);

        // Buscar "bebidas alcoholicas" debe encontrar "Bebidas Alcohólicas"
        if (config('database.default') === 'pgsql') {
            $results = Category::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%alcoholicas%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Bebidas Alcohólicas', $results->first()->name);
        }

        // Buscar "cafe" debe encontrar "Café y Té"
        if (config('database.default') === 'pgsql') {
            $results = Category::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%cafe%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Café y Té', $results->first()->name);
        }

        // Buscar "panaderia" debe encontrar "Panadería"
        if (config('database.default') === 'pgsql') {
            $results = Category::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%panaderia%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Panadería', $results->first()->name);
        }
    }

    /** @test */
    public function puede_buscar_categorias_por_slug_sin_acentos(): void
    {
        Category::factory()->create([
            'name' => 'Repostería',
            'slug' => 'reposteria',
        ]);

        Category::factory()->create([
            'name' => 'Carnicería',
            'slug' => 'carniceria',
        ]);

        // Buscar por slug sin acentos
        if (config('database.default') === 'pgsql') {
            $results = Category::whereRaw("unaccent(LOWER(slug::text)) LIKE unaccent(LOWER(?))", ['%reposteria%'])->get();
            $this->assertCount(1, $results);
            $this->assertEquals('Repostería', $results->first()->name);
        }
    }

    /** @test */
    public function busqueda_sin_acentos_es_case_insensitive(): void
    {
        Category::factory()->create(['name' => 'CAFÉ']);
        Category::factory()->create(['name' => 'café']);
        Category::factory()->create(['name' => 'Café']);

        // Buscar "cafe" en cualquier variación de mayúsculas/minúsculas
        if (config('database.default') === 'pgsql') {
            $results = Category::whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ['%cafe%'])->get();
            $this->assertCount(3, $results);
        }
    }
}
