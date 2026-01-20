<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStockMovementsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimos Movimientos de Stock')
            ->query(
                StockMovement::query()
                    ->with(['product', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale' => 'Venta',
                        'purchase' => 'Compra',
                        'adjustment' => 'Ajuste',
                        'waste' => 'Merma',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'danger',
                        'purchase' => 'success',
                        'adjustment' => 'warning',
                        'waste' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->formatStateUsing(function ($state, StockMovement $record) {
                        $prefix = in_array($record->type, ['purchase', 'adjustment']) && $state > 0 ? '+' : '';
                        return $prefix . $state;
                    })
                    ->color(fn ($state, StockMovement $record): string => 
                        in_array($record->type, ['purchase']) || ($record->type === 'adjustment' && $state > 0) 
                            ? 'success' 
                            : 'danger'
                    ),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(30)
                    ->wrap(),
            ])
            ->paginated(false);
    }
}
