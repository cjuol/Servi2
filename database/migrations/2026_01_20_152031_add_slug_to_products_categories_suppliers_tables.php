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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
