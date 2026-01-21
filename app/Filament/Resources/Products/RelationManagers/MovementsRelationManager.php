<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockMovement;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Histórico de Movimientos';

    protected static ?string $modelLabel = 'movimiento';

    protected static ?string $pluralModelLabel = 'movimientos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'adjustment' => 'info',
                        'waste' => 'danger',
                        'return' => 'warning',
                        'sale' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Compra',
                        'adjustment' => 'Ajuste',
                        'waste' => 'Merma',
                        'return' => 'Devolución',
                        'sale' => 'Venta',
                        default => $state,
                    }),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => $state >= 0 ? "+{$state}" : (string) $state),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->placeholder('Sin motivo'),
            ])
            ->recordUrl(fn (StockMovement $record): string => 
                StockMovementResource::getUrl('view', ['record' => $record])
            )
            ->defaultSort('created_at', 'desc')
            ->heading('Histórico de Movimientos de Stock')
            ->description('Registro de todos los movimientos de inventario realizados en este producto');
    }
}
