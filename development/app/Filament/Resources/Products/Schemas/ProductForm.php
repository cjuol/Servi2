<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name'),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('barcode')
                    ->label('Código de barras'),
                TextInput::make('sku')
                    ->label('SKU'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->label('Imagen')
                    ->image(),
                TextInput::make('cost_price')
                    ->label('Precio de coste (céntimos)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('¢'),
                TextInput::make('sale_price')
                    ->label('Precio de venta (céntimos)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('¢'),
                TextInput::make('tax_rate')
                    ->label('IVA (%)')
                    ->required()
                    ->numeric()
                    ->default(10),
                TextInput::make('stock_quantity')
                    ->label('Stock actual')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->default(0)
                    ->helperText('El stock solo se puede modificar mediante ajustes de inventario'),
                TextInput::make('low_stock_threshold')
                    ->label('Umbral de stock bajo')
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->required(),
                Toggle::make('track_stock')
                    ->label('Controlar stock')
                    ->required(),
            ]);
    }
}
