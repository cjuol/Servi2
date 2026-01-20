<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';

    /**
     * Obtiene la etiqueta legible del método de pago
     */
    public function getLabel(): string
    {
        return match($this) {
            self::CASH => 'Efectivo',
            self::CARD => 'Tarjeta',
        };
    }

    /**
     * Obtiene el color para Filament (opcional)
     */
    public function getColor(): string
    {
        return match($this) {
            self::CASH => 'success',
            self::CARD => 'info',
        };
    }
}
