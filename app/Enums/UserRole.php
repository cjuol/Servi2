<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case WAITER = 'waiter';

    /**
     * Obtiene la etiqueta legible para humanos.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::WAITER => 'Camarero',
        };
    }

    /**
     * Obtiene el color para badges en Filament.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::WAITER => 'success',
        };
    }
}
