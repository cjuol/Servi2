<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class EditProfile extends BaseEditProfile
{
    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('El nombre no puede ser modificado. Contacta con un administrador.'),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true, table: 'users', column: 'email')
                    ->maxLength(255),

                TextInput::make('current_password')
                    ->label('Contraseña Actual')
                    ->password()
                    ->dehydrated(false)
                    ->required(fn ($get) => filled($get('password')))
                    ->rules([
                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                            if (filled($value) && !Hash::check($value, auth()->user()->password)) {
                                $fail('La contraseña actual no es correcta.');
                            }
                        },
                    ])
                    ->helperText('Requerida solo si deseas cambiar tu contraseña.'),

                TextInput::make('password')
                    ->label('Nueva Contraseña')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->rules([PasswordRule::default()])
                    ->confirmed()
                    ->helperText('Dejar en blanco para mantener la contraseña actual.'),

                TextInput::make('password_confirmation')
                    ->label('Confirmar Nueva Contraseña')
                    ->password()
                    ->dehydrated(false)
                    ->required(fn ($get) => filled($get('password'))),
            ]);
    }
}
