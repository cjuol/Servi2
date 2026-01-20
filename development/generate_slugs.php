<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;

echo "Generando slugs para registros existentes...\n\n";

// Products
echo "Productos:\n";
Product::whereNull('slug')->orWhere('slug', '')->each(function ($product) {
    $slug = Str::slug($product->name);
    $count = 1;
    while (Product::where('slug', $slug)->exists()) {
        $slug = Str::slug($product->name) . '-' . $count;
        $count++;
    }
    $product->slug = $slug;
    $product->saveQuietly(); // Evita disparar eventos
    echo "  - {$product->name} → {$slug}\n";
});

// Categories
echo "\nCategorías:\n";
Category::whereNull('slug')->orWhere('slug', '')->each(function ($category) {
    $slug = Str::slug($category->name);
    $count = 1;
    while (Category::where('slug', $slug)->exists()) {
        $slug = Str::slug($category->name) . '-' . $count;
        $count++;
    }
    $category->slug = $slug;
    $category->saveQuietly();
    echo "  - {$category->name} → {$slug}\n";
});

// Suppliers
echo "\nProveedores:\n";
Supplier::whereNull('slug')->orWhere('slug', '')->each(function ($supplier) {
    $slug = Str::slug($supplier->name);
    $count = 1;
    while (Supplier::where('slug', $slug)->exists()) {
        $slug = Str::slug($supplier->name) . '-' . $count;
        $count++;
    }
    $supplier->slug = $slug;
    $supplier->saveQuietly();
    echo "  - {$supplier->name} → {$slug}\n";
});

// Users
echo "\nUsuarios:\n";
User::whereNull('slug')->orWhere('slug', '')->each(function ($user) {
    $slug = Str::slug($user->name);
    $count = 1;
    while (User::where('slug', $slug)->exists()) {
        $slug = Str::slug($user->name) . '-' . $count;
        $count++;
    }
    $user->slug = $slug;
    $user->saveQuietly();
    echo "  - {$user->name} → {$slug}\n";
});

echo "\n¡Slugs generados exitosamente!\n";
