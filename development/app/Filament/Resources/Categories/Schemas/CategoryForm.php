<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                ColorPicker::make('color')
                    ->label('Color')
                    ->hiddenLabel(false)
                    ->formatStateUsing(fn ($state) => $state),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->required(),
            ]);
    }
}
