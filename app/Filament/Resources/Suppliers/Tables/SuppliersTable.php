<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => route('filament.admin.resources.proveedores.view', ['record' => $record->slug]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(query: function ($query, $search) {
                        if (config('database.default') === 'pgsql') {
                            return $query->whereRaw("unaccent(LOWER(name::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                        }
                        return $query->where('name', 'ILIKE', "%{$search}%");
                    }),
                TextColumn::make('contact_name')
                    ->label('Persona de contacto')
                    ->searchable(query: function ($query, $search) {
                        if (config('database.default') === 'pgsql') {
                            return $query->whereRaw("unaccent(LOWER(contact_name::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                        }
                        return $query->where('contact_name', 'ILIKE', "%{$search}%");
                    }),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(query: function ($query, $search) {
                        if (config('database.default') === 'pgsql') {
                            return $query->whereRaw("unaccent(LOWER(email::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                        }
                        return $query->where('email', 'ILIKE', "%{$search}%");
                    }),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(query: function ($query, $search) {
                        if (config('database.default') === 'pgsql') {
                            return $query->whereRaw("unaccent(LOWER(phone::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                        }
                        return $query->where('phone', 'ILIKE', "%{$search}%");
                    }),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
