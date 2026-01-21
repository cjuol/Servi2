<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bebidas',
                'slug' => 'bebidas',
                'color' => '#3B82F6', // Azul
                'is_active' => true,
            ],
            [
                'name' => 'Cafés',
                'slug' => 'cafes',
                'color' => '#92400E', // Marrón
                'is_active' => true,
            ],
            [
                'name' => 'Entrantes',
                'slug' => 'entrantes',
                'color' => '#10B981', // Verde
                'is_active' => true,
            ],
            [
                'name' => 'Platos Principales',
                'slug' => 'platos-principales',
                'color' => '#EF4444', // Rojo
                'is_active' => true,
            ],
            [
                'name' => 'Postres',
                'slug' => 'postres',
                'color' => '#F59E0B', // Naranja
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
