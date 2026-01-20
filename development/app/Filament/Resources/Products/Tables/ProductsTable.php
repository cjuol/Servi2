<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->label('Código de barras')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                ImageColumn::make('image_path')
                    ->label('Imagen'),
                TextColumn::make('cost_price')
                    ->label('Precio coste')
                    ->money()
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Precio venta')
                    ->money()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->label('IVA (%)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label('Umbral bajo')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                IconColumn::make('track_stock')
                    ->label('Controlar stock')
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
                Action::make('adjust_stock')
                    ->label('Ajustar Stock')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->helperText('Usa números positivos para entradas y negativos para salidas'),
                        Select::make('type')
                            ->label('Tipo de movimiento')
                            ->required()
                            ->options([
                                StockMovement::TYPE_PURCHASE => 'Compra',
                                StockMovement::TYPE_ADJUSTMENT => 'Ajuste',
                                StockMovement::TYPE_WASTE => 'Merma',
                                'return' => 'Devolución',
                            ]),
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->rows(3)
                            ->placeholder('Describe el motivo del ajuste (opcional)'),
                    ])
                    ->action(function ($record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            // Crear el registro de movimiento
                            StockMovement::create([
                                'product_id' => $record->id,
                                'user_id' => auth()->id(),
                                'quantity' => $data['quantity'],
                                'type' => $data['type'],
                                'reason' => $data['reason'] ?? null,
                            ]);

                            // Actualizar el stock del producto
                            $record->increment('stock_quantity', $data['quantity']);
                        });

                        Notification::make()
                            ->success()
                            ->title('Stock ajustado correctamente')
                            ->body("Se ha modificado el stock en {$data['quantity']} unidades.")
                            ->send();
                    }),
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
