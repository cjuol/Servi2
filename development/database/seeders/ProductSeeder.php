<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías y proveedores
        $bebidas = Category::where('slug', 'bebidas')->first();
        $cafes = Category::where('slug', 'cafes')->first();
        $entrantes = Category::where('slug', 'entrantes')->first();
        $platos = Category::where('slug', 'platos-principales')->first();
        $postres = Category::where('slug', 'postres')->first();

        $distribuidorBebidas = Supplier::where('name', 'Distribuciones Bebidas SL')->first();
        $carniceria = Supplier::where('name', 'Carnicería Selecta')->first();
        $gourmet = Supplier::where('name', 'Productos Gourmet SA')->first();

        $products = [
            // BEBIDAS (precios en céntimos: 250 = 2.50€)
            [
                'category_id' => $bebidas->id,
                'supplier_id' => $distribuidorBebidas->id,
                'name' => 'Coca-Cola 33cl',
                'barcode' => '8410054200020',
                'sku' => 'BEB-001',
                'cost_price' => 80,      // 0.80€
                'sale_price' => 250,     // 2.50€
                'tax_rate' => 1000,      // 10%
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $bebidas->id,
                'supplier_id' => $distribuidorBebidas->id,
                'name' => 'Agua Mineral 50cl',
                'barcode' => '8410054200021',
                'sku' => 'BEB-002',
                'cost_price' => 30,
                'sale_price' => 150,
                'tax_rate' => 1000,
                'stock_quantity' => 100,
                'low_stock_threshold' => 20,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $bebidas->id,
                'supplier_id' => $distribuidorBebidas->id,
                'name' => 'Cerveza Estrella Galicia',
                'barcode' => '8410054200022',
                'sku' => 'BEB-003',
                'cost_price' => 90,
                'sale_price' => 300,
                'tax_rate' => 2100,      // 21%
                'stock_quantity' => 8,   // STOCK BAJO
                'low_stock_threshold' => 15,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $bebidas->id,
                'supplier_id' => $distribuidorBebidas->id,
                'name' => 'Vino Tinto Crianza',
                'barcode' => '8410054200023',
                'sku' => 'BEB-004',
                'cost_price' => 600,
                'sale_price' => 1500,
                'tax_rate' => 2100,
                'stock_quantity' => 25,
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],

            // CAFÉS
            [
                'category_id' => $cafes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Café Solo',
                'sku' => 'CAF-001',
                'cost_price' => 20,
                'sale_price' => 120,
                'tax_rate' => 1000,
                'stock_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_active' => true,
                'track_stock' => false,  // No trackea stock
            ],
            [
                'category_id' => $cafes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Café con Leche',
                'sku' => 'CAF-002',
                'cost_price' => 25,
                'sale_price' => 150,
                'tax_rate' => 1000,
                'stock_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_active' => true,
                'track_stock' => false,
            ],
            [
                'category_id' => $cafes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Cappuccino',
                'sku' => 'CAF-003',
                'cost_price' => 30,
                'sale_price' => 180,
                'tax_rate' => 1000,
                'stock_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_active' => true,
                'track_stock' => false,
            ],

            // ENTRANTES
            [
                'category_id' => $entrantes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Ensalada Mixta',
                'sku' => 'ENT-001',
                'cost_price' => 200,
                'sale_price' => 650,
                'tax_rate' => 1000,
                'stock_quantity' => 0,
                'low_stock_threshold' => 0,
                'is_active' => true,
                'track_stock' => false,
            ],
            [
                'category_id' => $entrantes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Croquetas Caseras (6 uds)',
                'sku' => 'ENT-002',
                'cost_price' => 150,
                'sale_price' => 550,
                'tax_rate' => 1000,
                'stock_quantity' => 30,
                'low_stock_threshold' => 10,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $entrantes->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Patatas Bravas',
                'sku' => 'ENT-003',
                'cost_price' => 100,
                'sale_price' => 450,
                'tax_rate' => 1000,
                'stock_quantity' => 3,   // STOCK MUY BAJO
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],

            // PLATOS PRINCIPALES
            [
                'category_id' => $platos->id,
                'supplier_id' => $carniceria->id,
                'name' => 'Entrecot de Ternera',
                'sku' => 'PLA-001',
                'cost_price' => 800,
                'sale_price' => 1850,
                'tax_rate' => 1000,
                'stock_quantity' => 15,
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $platos->id,
                'supplier_id' => $carniceria->id,
                'name' => 'Pollo al Ajillo',
                'sku' => 'PLA-002',
                'cost_price' => 400,
                'sale_price' => 1200,
                'tax_rate' => 1000,
                'stock_quantity' => 20,
                'low_stock_threshold' => 8,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $platos->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Paella Valenciana',
                'sku' => 'PLA-003',
                'cost_price' => 500,
                'sale_price' => 1400,
                'tax_rate' => 1000,
                'stock_quantity' => 5,   // STOCK BAJO
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $platos->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Merluza a la Plancha',
                'sku' => 'PLA-004',
                'cost_price' => 600,
                'sale_price' => 1650,
                'tax_rate' => 1000,
                'stock_quantity' => 12,
                'low_stock_threshold' => 6,
                'is_active' => true,
                'track_stock' => true,
            ],

            // POSTRES
            [
                'category_id' => $postres->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Tarta de Queso',
                'sku' => 'POS-001',
                'cost_price' => 150,
                'sale_price' => 500,
                'tax_rate' => 1000,
                'stock_quantity' => 10,
                'low_stock_threshold' => 3,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $postres->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Flan Casero',
                'sku' => 'POS-002',
                'cost_price' => 80,
                'sale_price' => 350,
                'tax_rate' => 1000,
                'stock_quantity' => 20,
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $postres->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Helado (3 bolas)',
                'sku' => 'POS-003',
                'cost_price' => 120,
                'sale_price' => 450,
                'tax_rate' => 1000,
                'stock_quantity' => 2,   // STOCK MUY BAJO
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $postres->id,
                'supplier_id' => $gourmet->id,
                'name' => 'Tiramisú',
                'sku' => 'POS-004',
                'cost_price' => 180,
                'sale_price' => 550,
                'tax_rate' => 1000,
                'stock_quantity' => 15,
                'low_stock_threshold' => 5,
                'is_active' => true,
                'track_stock' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
