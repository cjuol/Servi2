<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('role')
                    ->label('Rol')
                    ->options([
                        UserRole::ADMIN->value => UserRole::ADMIN->getLabel(),
                        UserRole::WAITER->value => UserRole::WAITER->getLabel(),
                    ])
                    ->required()
                    ->default(UserRole::WAITER->value)
                    ->native(false),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->helperText('Dejar en blanco para mantener la contraseña actual al editar.'),

                DateTimePicker::make('email_verified_at')
                    ->label('Email Verificado')
                    ->default(now()),
            ]);
    }
}

