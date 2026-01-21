<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Mi Perfil';
    
    protected static ?string $navigationLabel = 'Perfil';
    
    public function getHeading(): string
    {
        return 'Editar Perfil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label('Foto de Perfil')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->helperText('Sube una imagen para tu perfil. Máximo 2MB.'),

                $this->getNameFormComponent()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('El nombre no puede ser modificado. Contacta con un administrador.'),

                $this->getEmailFormComponent(),

                $this->getCurrentPasswordFormComponent()
                    ->helperText('Requerida solo si deseas cambiar tu contraseña.'),

                $this->getPasswordFormComponent()
                    ->helperText('Dejar en blanco para mantener la contraseña actual.'),

                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
