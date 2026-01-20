<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => route('filament.admin.resources.categorias.view', ['record' => $record->slug]))
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
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(query: function ($query, $search) {
                        if (config('database.default') === 'pgsql') {
                            return $query->whereRaw("unaccent(LOWER(slug::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                        }
                        return $query->where('slug', 'ILIKE', "%{$search}%");
                    }),
                ColorColumn::make('color')
                    ->label('Color'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
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
                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
