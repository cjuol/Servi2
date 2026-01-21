<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('contact_name')
                    ->label('Persona de contacto'),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
            ]);
    }
}
