<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pedidos/Comandas
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('user_id')->constrained(); // Camarero que tomó el pedido
            
            $table->string('status')->default('open'); // open, closed, cancelled
            $table->integer('total')->default(0); // Total en céntimos
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });

        // Ítems del Pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained();
            
            $table->integer('quantity')->default(1);
            $table->integer('unit_price'); // Precio en el momento de la venta (histórico)
            $table->integer('tax_rate'); // IVA en el momento de la venta
            $table->integer('subtotal'); // quantity * unit_price
            
            $table->text('notes')->nullable(); // Ej: "Sin cebolla"
            
            $table->timestamps();
            
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
