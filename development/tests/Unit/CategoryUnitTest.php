<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tiene_fillable_correcto(): void
    {
        $category = new Category();

        $expectedFillable = [
            'name',
            'slug',
            'color',
            'is_active',
        ];

        $this->assertEquals($expectedFillable, $category->getFillable());
    }

    /** @test */
    public function tiene_casts_correcto(): void
    {
        $category = new Category();

        $casts = $category->getCasts();

        $this->assertArrayHasKey('is_active', $casts);
        $this->assertEquals('boolean', $casts['is_active']);
    }

    /** @test */
    public function usa_soft_deletes(): void
    {
        $category = Category::factory()->create();

        $this->assertNull($category->deleted_at);

        $category->delete();

        $this->assertNotNull($category->fresh()->deleted_at);
    }

    /** @test */
    public function relacion_products_esta_definida(): void
    {
        $category = Category::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $category->products()
        );
    }

    /** @test */
    public function scope_active_filtra_correctamente(): void
    {
        $activeCategory = Category::factory()->active()->create();
        $inactiveCategory = Category::factory()->inactive()->create();

        $result = Category::active()->get();

        $this->assertTrue($result->contains($activeCategory));
        $this->assertFalse($result->contains($inactiveCategory));
    }

    /** @test */
    public function modelo_usa_uuid_trait(): void
    {
        $reflection = new \ReflectionClass(Category::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            $traits
        );
    }

    /** @test */
    public function modelo_usa_soft_deletes_trait(): void
    {
        $reflection = new \ReflectionClass(Category::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\SoftDeletes',
            $traits
        );
    }

    /** @test */
    public function puede_crear_instancia_con_factory(): void
    {
        $category = Category::factory()->make();

        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->slug);
    }

    /** @test */
    public function factory_genera_datos_validos(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->name);
        $this->assertNotNull($category->slug);
        $this->assertNotNull($category->color);
        $this->assertIsBool($category->is_active);
    }

    /** @test */
    public function factory_state_active_funciona(): void
    {
        $category = Category::factory()->active()->create();

        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function factory_state_inactive_funciona(): void
    {
        $category = Category::factory()->inactive()->create();

        $this->assertFalse($category->is_active);
    }
}
