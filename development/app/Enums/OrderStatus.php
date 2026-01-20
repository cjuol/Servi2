<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    /**
     * Obtiene la etiqueta legible del estado
     */
    public function getLabel(): string
    {
        return match($this) {
            self::OPEN => 'Abierto',
            self::CLOSED => 'Cerrado',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Obtiene el color para Filament
     */
    public function getColor(): string
    {
        return match($this) {
            self::OPEN => 'warning',
            self::CLOSED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
