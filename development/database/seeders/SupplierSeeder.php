<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Distribuciones Bebidas SL',
                'contact_name' => 'Pedro Martínez',
                'email' => 'pedidos@distribebidas.com',
                'phone' => '+34 912 345 678',
            ],
            [
                'name' => 'Carnicería Selecta',
                'contact_name' => 'Ana Ruiz',
                'email' => 'info@carniceriaselecta.es',
                'phone' => '+34 913 456 789',
            ],
            [
                'name' => 'Productos Gourmet SA',
                'contact_name' => 'Luis González',
                'email' => 'ventas@productosgourmet.com',
                'phone' => '+34 914 567 890',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
