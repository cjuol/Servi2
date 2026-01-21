<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categorías (Ej: Bebidas, Carnes, Postres)
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique(); // Para URLs amigables o búsquedas rápidas
            $table->string('color')->nullable(); // Para pintar el botón en el TPV (Ej: #FF0000)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Importante: No borrar datos históricos
        });

        // 2. Proveedores (Ej: Makro, Carnicería Pepe)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // 3. Productos (El núcleo)
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relaciones
            $table->foreignUuid('category_id')->constrained()->nullOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained()->nullOnDelete();

            // Datos básicos
            $table->string('name');
            $table->string('barcode')->nullable()->unique(); // Para lector de códigos
            $table->string('sku')->nullable()->unique();     // Código interno
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            // Precios (Guardar SIEMPRE en centavos/enteros para evitar errores de redondeo)
            $table->integer('cost_price')->default(0); // Precio de compra (ej: 1000 = 10.00€)
            $table->integer('sale_price')->default(0); // Precio de venta (ej: 2550 = 25.50€)
            $table->integer('tax_rate')->default(10);  // IVA (10%, 21%)

            // Stock Actual (Caché rápida)
            $table->integer('stock_quantity')->default(0); 
            $table->integer('low_stock_threshold')->default(5); // Avisar si baja de 5
            
            // Control
            $table->boolean('is_active')->default(true); // ¿Aparece en el TPV?
            $table->boolean('track_stock')->default(true); // Servicios o agua de grifo no trackean stock

            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Movimientos de Stock (Kardex / Auditoría)
        // CRÍTICO: Nunca modifiques 'stock_quantity' en Product sin crear un registro aquí.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained(); // ¿Quién hizo el cambio?
            
            $table->integer('quantity'); // Positivo (compra) o Negativo (venta/merma)
            $table->string('type'); // Enum: 'sale', 'purchase', 'adjustment', 'waste'
            $table->string('reason')->nullable(); // Ej: "Se cayó al suelo", "Pedido #123"
            
            $table->timestamps();
            
            // Índices para que los reportes vuelen en Postgres
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
    }
};