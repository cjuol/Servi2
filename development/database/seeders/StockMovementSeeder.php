<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $admin = User::where('email', 'admin@admin.com')->first();
        $users = User::all();

        if ($products->isEmpty() || !$admin) {
            return;
        }

        $movements = [];
        $currentDate = now();

        foreach ($products as $product) {
            // Solo generar movimientos para productos que trackean stock
            if (!$product->track_stock) {
                continue;
            }

            // Movimiento inicial de compra (hace 30 días)
            $movements[] = [
                'id' => \Illuminate\Support\Str::uuid(),
                'product_id' => $product->id,
                'user_id' => $admin->id,
                'quantity' => $product->stock_quantity + rand(10, 30),
                'type' => StockMovement::TYPE_PURCHASE,
                'reason' => 'Stock inicial del sistema',
                'created_at' => $currentDate->copy()->subDays(30),
                'updated_at' => $currentDate->copy()->subDays(30),
            ];

            // Generar entre 3-8 movimientos aleatorios para cada producto
            $numMovements = rand(3, 8);
            
            for ($i = 0; $i < $numMovements; $i++) {
                $daysAgo = rand(1, 29);
                $randomUser = $users->random();
                $movementType = $this->getRandomMovementType();
                
                $quantity = match ($movementType) {
                    StockMovement::TYPE_SALE => -rand(1, 5),
                    StockMovement::TYPE_PURCHASE => rand(10, 50),
                    StockMovement::TYPE_ADJUSTMENT => rand(-10, 10),
                    StockMovement::TYPE_WASTE => -rand(1, 3),
                    default => 0,
                };

                $reason = $this->getReasonForMovementType($movementType, $quantity);

                $movements[] = [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'product_id' => $product->id,
                    'user_id' => $randomUser->id,
                    'quantity' => $quantity,
                    'type' => $movementType,
                    'reason' => $reason,
                    'created_at' => $currentDate->copy()->subDays($daysAgo),
                    'updated_at' => $currentDate->copy()->subDays($daysAgo),
                ];
            }
        }

        // Ordenar por fecha y insertar
        usort($movements, function ($a, $b) {
            return $a['created_at'] <=> $b['created_at'];
        });

        // Insertar en bloques para mejor rendimiento
        foreach (array_chunk($movements, 100) as $chunk) {
            StockMovement::insert($chunk);
        }
    }

    private function getRandomMovementType(): string
    {
        $types = [
            StockMovement::TYPE_SALE,
            StockMovement::TYPE_PURCHASE,
            StockMovement::TYPE_ADJUSTMENT,
            StockMovement::TYPE_WASTE,
        ];

        // Probabilidades: más ventas y compras que ajustes y mermas
        $weights = [40, 35, 15, 10]; // Sale, Purchase, Adjustment, Waste
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $types[$index];
            }
        }
        
        return StockMovement::TYPE_SALE;
    }

    private function getReasonForMovementType(string $type, int $quantity): string
    {
        return match ($type) {
            StockMovement::TYPE_SALE => 'Venta al cliente',
            StockMovement::TYPE_PURCHASE => 'Recepción de mercancía del proveedor',
            StockMovement::TYPE_ADJUSTMENT => $quantity > 0 
                ? 'Ajuste por inventario - stock adicional encontrado' 
                : 'Ajuste por inventario - corrección de diferencias',
            StockMovement::TYPE_WASTE => 'Producto dañado o caducado',
            default => 'Movimiento de stock',
        };
    }
}

