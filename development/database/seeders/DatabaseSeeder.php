<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden importante: Primero usuarios, luego tablas, categorías, proveedores y finalmente productos
        $this->call([
            UserSeeder::class,
            RestaurantTableSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            StockMovementSeeder::class,
        ]);
    }
}
