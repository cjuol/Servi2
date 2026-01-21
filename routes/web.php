<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pos\OrderTerminal;

/*Route::get('/', function () {
    return view('welcome');
});
*/

// Ruta del TPV (Terminal Punto de Venta) - Requiere autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', OrderTerminal::class)->name('pos.terminal');
    
    // Ruta para generar el ticket térmico
    Route::get('/pos/ticket/{order}', function (Order $order) {
        // Verificar que el usuario autenticado tenga acceso a este pedido
        // (opcional: puedes añadir lógica de autorización más compleja)
        return view('pos.ticket', compact('order'));
    })->name('pos.ticket');
});