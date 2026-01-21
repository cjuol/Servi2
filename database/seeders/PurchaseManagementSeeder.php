<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener proveedores y productos existentes
        $suppliers = Supplier::all();
        
        if ($suppliers->isEmpty()) {
            $this->command->info('No hay proveedores. Creando proveedores de ejemplo...');
            $suppliers = Supplier::factory(3)->create();
        }

        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('No hay productos. No se pueden crear presupuestos.');
            return;
        }

        $this->command->info('Creando presupuestos...');

        // Crear 5 presupuestos con sus líneas
        foreach ($suppliers->take(3) as $supplier) {
            $budget = Budget::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            // Agregar entre 3 y 7 líneas al presupuesto
            $randomProducts = $products->random(rand(3, min(7, $products->count())));
            
            foreach ($randomProducts as $product) {
                $quantity = rand(5, 50);
                $unitTaxBase = (int) ($product->cost_price * 0.9); // Base sin IVA
                $taxBase = $quantity * $unitTaxBase;
                $taxRateQuantity = (int) ($taxBase * $product->tax_rate / 100);

                BudgetDetail::create([
                    'budget_id' => $budget->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'tax_base' => $taxBase,
                    'tax_rate_quantity' => $taxRateQuantity,
                    'total' => $taxBase + $taxRateQuantity,
                    'notes' => fake()->optional(0.3)->sentence(),
                ]);
            }

            $this->command->info("✓ Presupuesto creado para {$supplier->name}");
        }

        $this->command->info('Creando albaranes de entrega...');

        // Crear algunos albaranes vinculados a presupuestos
        $budgets = Budget::with('details.product')->get();
        
        foreach ($budgets->take(2) as $budget) {
            $deliveryNote = DeliveryNote::create([
                'budget_id' => $budget->id,
                'invoice_id' => null,
                'date' => now()->subDays(rand(1, 10)),
                'tax_base' => 0,
                'tax_rate_quantity' => 0,
                'stored' => false,
            ]);

            // Copiar las líneas del presupuesto al albarán
            foreach ($budget->details as $budgetDetail) {
                DeliveryNoteDetail::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $budgetDetail->product_id,
                    'quantity' => $budgetDetail->quantity,
                    'tax_base' => $budgetDetail->tax_base,
                    'tax_rate_quantity' => $budgetDetail->tax_rate_quantity,
                    'total' => $budgetDetail->total,
                ]);
            }

            $this->command->info("✓ Albarán creado desde presupuesto #{$budget->id}");
        }

        $this->command->info('Creando facturas...');

        // Crear algunas facturas
        foreach ($suppliers->take(2) as $supplier) {
            Invoice::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $this->command->info("✓ Factura creada para {$supplier->name}");
        }

        $this->command->info('');
        $this->command->info('✅ Sistema de gestión de compras poblado exitosamente!');
        $this->command->info('   - Presupuestos: ' . Budget::count());
        $this->command->info('   - Líneas de presupuesto: ' . BudgetDetail::count());
        $this->command->info('   - Albaranes: ' . DeliveryNote::count());
        $this->command->info('   - Líneas de albarán: ' . DeliveryNoteDetail::count());
        $this->command->info('   - Facturas: ' . Invoice::count());
    }
}
