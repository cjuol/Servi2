<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mesas del Restaurante
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Ej: "Mesa 1", "Terraza 3"
            $table->integer('capacity')->default(4); // Número de comensales
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
