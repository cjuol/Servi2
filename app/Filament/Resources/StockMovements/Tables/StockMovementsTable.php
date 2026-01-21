<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\Order;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
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
                    })
                    ->sortable(),
                    
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
                    )
                    ->sortable(),
                    
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sistema'),
                    
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40)
                    ->searchable()
                    ->placeholder('Sin motivo'),
                    
                TextColumn::make('order.ticket_number')
                    ->label('Ticket')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->url(fn (StockMovement $record): ?string => 
                        $record->order_id 
                            ? route('pos.ticket', ['order' => $record->order_id]) 
                            : null
                    )
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        StockMovement::TYPE_SALE => 'Venta',
                        StockMovement::TYPE_PURCHASE => 'Compra',
                        StockMovement::TYPE_ADJUSTMENT => 'Ajuste',
                        StockMovement::TYPE_WASTE => 'Merma',
                    ]),
                    
                SelectFilter::make('product')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('view_ticket')
                    ->label('Ver Ticket')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (StockMovement $record): bool => $record->order_id !== null)
                    ->modalHeading(fn (StockMovement $record): string => 
                        'Ticket #' . $record->order->ticket_number
                    )
                    ->modalContent(function (StockMovement $record): HtmlString {
                        $order = Order::with(['items.product', 'user'])
                            ->find($record->order_id);
                        
                        if (!$order) {
                            return new HtmlString('<p class="text-red-600">Orden no encontrada</p>');
                        }
                        
                        // Renderizar la vista del ticket
                        $html = view('pos.ticket', ['order' => $order])->render();
                        
                        return new HtmlString($html);
                    })
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
