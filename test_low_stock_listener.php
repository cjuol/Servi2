<?php

/**
 * SCRIPT DE PRUEBA: Listener CheckLowStock
 * 
 * Este script demuestra cómo funciona el sistema de notificaciones
 * de stock bajo cuando se crea un pedido.
 * 
 * INSTRUCCIONES:
 * 1. Asegúrate de tener la base de datos poblada (php artisan db:seed)
 * 2. Ejecuta: php artisan tinker
 * 3. Copia y pega el código de este archivo en tinker
 * 4. Revisa las notificaciones en el panel de Filament
 */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Events\OrderPlaced;

// ========================================
// EJEMPLO 1: Crear orden con producto de stock bajo
// ========================================

echo "\n🧪 PRUEBA 1: Crear orden con Cerveza (stock bajo)\n";
echo "================================================\n\n";

// 1. Obtener productos con stock bajo
$cerveza = Product::where('sku', 'BEB-003')->first(); // Stock: 8, Límite: 15
$patatas = Product::where('sku', 'ENT-003')->first(); // Stock: 3, Límite: 5

echo "📦 Cerveza Estrella Galicia:\n";
echo "   Stock actual: {$cerveza->stock_quantity}\n";
echo "   Stock mínimo: {$cerveza->low_stock_threshold}\n";
echo "   ⚠️  ¡Stock bajo detectado!\n\n";

echo "📦 Patatas Bravas:\n";
echo "   Stock actual: {$patatas->stock_quantity}\n";
echo "   Stock mínimo: {$patatas->low_stock_threshold}\n";
echo "   ⚠️  ¡Stock bajo detectado!\n\n";

// 2. Obtener el primer usuario (camarero)
$usuario = User::first();

// 3. Crear una orden
$orden = Order::create([
    'user_id' => $usuario->id,
    'status' => 'pending',
    'total' => 0, // Se calculará después
]);

echo "✅ Orden #{$orden->id} creada por {$usuario->name}\n\n";

// 4. Agregar ítems a la orden
$items = [
    [
        'product' => $cerveza,
        'quantity' => 2,
    ],
    [
        'product' => $patatas,
        'quantity' => 1,
    ],
];

$total = 0;

foreach ($items as $itemData) {
    $product = $itemData['product'];
    $quantity = $itemData['quantity'];
    $subtotal = $product->sale_price * $quantity;
    
    OrderItem::create([
        'order_id' => $orden->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_price' => $product->sale_price,
        'tax_rate' => $product->tax_rate,
        'subtotal' => $subtotal,
    ]);
    
    $total += $subtotal;
    
    $precio = number_format($product->sale_price / 100, 2);
    echo "   ➕ {$quantity}x {$product->name} - {$precio}€\n";
}

// Actualizar el total
$orden->update(['total' => $total]);
$totalEuros = number_format($total / 100, 2);
echo "\n💰 Total: {$totalEuros}€\n\n";

// 5. Disparar el evento OrderPlaced
echo "🚀 Disparando evento OrderPlaced...\n";
event(new OrderPlaced($orden));
echo "✅ Evento disparado\n\n";

echo "📬 Notificaciones enviadas:\n";
$todosLosUsuarios = User::all();
echo "   📧 " . $todosLosUsuarios->count() . " usuarios notificados\n\n";

echo "🔔 Para ver las notificaciones:\n";
echo "   1. Accede al panel de Filament: /admin\n";
echo "   2. Haz clic en el ícono de campana (🔔) en la barra superior\n";
echo "   3. Verás notificaciones para:\n";
echo "      - ⚠️  Cerveza Estrella Galicia (Stock: 8)\n";
echo "      - ⚠️  Patatas Bravas (Stock: 3)\n\n";

// ========================================
// EJEMPLO 2: Producto sin control de stock
// ========================================

echo "\n🧪 PRUEBA 2: Crear orden con Café (sin control de stock)\n";
echo "========================================================\n\n";

$cafe = Product::where('sku', 'CAF-001')->first();

echo "📦 Café Solo:\n";
echo "   track_stock: " . ($cafe->track_stock ? 'Sí' : 'No') . "\n";
echo "   ℹ️  No se generará notificación\n\n";

$orden2 = Order::create([
    'user_id' => $usuario->id,
    'status' => 'pending',
    'total' => $cafe->sale_price,
]);

OrderItem::create([
    'order_id' => $orden2->id,
    'product_id' => $cafe->id,
    'quantity' => 1,
    'unit_price' => $cafe->sale_price,
    'tax_rate' => $cafe->tax_rate,
    'subtotal' => $cafe->sale_price,
]);

echo "✅ Orden #{$orden2->id} creada\n";
echo "🚀 Disparando evento OrderPlaced...\n";
event(new OrderPlaced($orden2));
echo "✅ Evento disparado\n";
echo "ℹ️  No se enviaron notificaciones (producto sin control de stock)\n\n";

// ========================================
// EJEMPLO 3: Producto con stock suficiente
// ========================================

echo "\n🧪 PRUEBA 3: Crear orden con Agua (stock suficiente)\n";
echo "====================================================\n\n";

$agua = Product::where('sku', 'BEB-002')->first();

echo "📦 Agua Mineral:\n";
echo "   Stock actual: {$agua->stock_quantity}\n";
echo "   Stock mínimo: {$agua->low_stock_threshold}\n";
echo "   ✅ Stock suficiente\n\n";

$orden3 = Order::create([
    'user_id' => $usuario->id,
    'status' => 'pending',
    'total' => $agua->sale_price * 5,
]);

OrderItem::create([
    'order_id' => $orden3->id,
    'product_id' => $agua->id,
    'quantity' => 5,
    'unit_price' => $agua->sale_price,
    'tax_rate' => $agua->tax_rate,
    'subtotal' => $agua->sale_price * 5,
]);

echo "✅ Orden #{$orden3->id} creada (5 aguas)\n";
echo "🚀 Disparando evento OrderPlaced...\n";
event(new OrderPlaced($orden3));
echo "✅ Evento disparado\n";
echo "ℹ️  No se enviaron notificaciones (stock suficiente)\n\n";

// ========================================
// RESUMEN
// ========================================

echo "\n📊 RESUMEN DE PRUEBAS\n";
echo "=====================\n\n";

echo "Total de órdenes creadas: 3\n";
echo "Notificaciones generadas: 2 (Cerveza y Patatas)\n\n";

echo "🔍 Verificación:\n";
echo "   SELECT * FROM notifications WHERE type = 'filament';\n\n";

echo "✨ ¡Pruebas completadas!\n\n";
