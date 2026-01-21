<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pos\OrderTerminal;

/*Route::get('/', function () {
    return view('welcome');
});
*/

// Ruta del TPV (Terminal Punto de Venta) - Requiere autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', OrderTerminal::class)->name('pos.terminal');
});