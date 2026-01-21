<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class CheckLowStock
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        // Recorrer los ítems de la orden
        foreach ($event->order->items as $item) {
            $product = $item->product;

            // Verificar si el producto tiene control de stock activado
            if (!$product->track_stock) {
                continue;
            }

            // Comprobar si el stock actual es menor o igual al stock de seguridad
            if ($product->stock_quantity <= $product->low_stock_threshold) {
                // Enviar notificación de Filament a todos los usuarios
                Notification::make()
                    ->warning()
                    ->title("⚠️ Stock Bajo: {$product->name}")
                    ->body("Quedan {$product->stock_quantity} unidades. Stock de seguridad: {$product->low_stock_threshold}")
                    ->actions([
                        Action::make('view')
                            ->label('Ver Producto')
                            ->url(route('filament.admin.resources.productos.view', ['record' => $product->slug]))
                            ->button()
                            ->markAsRead(),
                    ])
                    ->sendToDatabase(User::all());
            }
        }
    }
}
