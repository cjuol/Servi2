<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cambios estructurales:
     * 1. Eliminar stripe_payment_id de orders (ya no se usa)
     * 2. Añadir order_id a stock_movements para trazabilidad completa
     */
    public function up(): void
    {
        // 1. Eliminar stripe_payment_id de orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stripe_payment_id');
        });

        // 2. Añadir order_id a stock_movements con foreign key
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignUuid('order_id')
                ->nullable()
                ->after('delivery_note_id')
                ->constrained('orders')
                ->nullOnDelete();
            
            // Índice para consultas rápidas de movimientos por pedido
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en orden inverso
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_payment_id')->nullable()->after('ticket_number');
        });
    }
};
