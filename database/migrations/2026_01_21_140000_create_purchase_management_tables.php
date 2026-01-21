<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Presupuestos de proveedores
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained()->restrictOnDelete();
            $table->timestamp('date');
            $table->integer('tax_base')->default(0)->comment('Base imponible total en céntimos');
            $table->integer('tax_rate_quantity')->default(0)->comment('Total de impuestos en céntimos');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'date']);
        });

        // 2. Líneas de detalle de presupuestos
        Schema::create('budget_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('tax_base')->comment('Base imponible por línea en céntimos');
            $table->integer('tax_rate_quantity')->comment('Impuesto por línea en céntimos');
            $table->integer('total')->comment('Total de la línea en céntimos');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('budget_id');
            $table->index('product_id');
        });

        // 3. Facturas de proveedores
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained()->restrictOnDelete();
            $table->timestamp('date');
            $table->integer('tax_base')->default(0)->comment('Base imponible total en céntimos');
            $table->integer('tax_rate_quantity')->default(0)->comment('Total de impuestos en céntimos');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'date']);
        });

        // 4. Albaranes de entrega
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('date');
            $table->integer('tax_base')->default(0)->comment('Base imponible total en céntimos');
            $table->integer('tax_rate_quantity')->default(0)->comment('Total de impuestos en céntimos');
            $table->boolean('stored')->default(false)->comment('Si true, el stock ya se sumó');
            $table->timestamps();
            $table->softDeletes();

            $table->index('budget_id');
            $table->index('invoice_id');
            $table->index(['stored', 'date']);
        });

        // 5. Líneas de detalle de albaranes
        Schema::create('delivery_note_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('tax_base')->comment('Base imponible por línea en céntimos');
            $table->integer('tax_rate_quantity')->comment('Impuesto por línea en céntimos');
            $table->integer('total')->comment('Total de la línea en céntimos');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('delivery_note_id');
            $table->index('product_id');
        });

        // 6. Agregar columna delivery_note_id a stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignUuid('delivery_note_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete()
                ->comment('Link al albarán si es compra');
            
            $table->index('delivery_note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['delivery_note_id']);
            $table->dropColumn('delivery_note_id');
        });

        Schema::dropIfExists('delivery_note_details');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('budget_details');
        Schema::dropIfExists('budgets');
    }
};
