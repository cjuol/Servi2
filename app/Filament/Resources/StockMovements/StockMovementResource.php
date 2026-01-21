<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Resources\StockMovements\Pages\ViewStockMovement;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Movimientos de Stock';

    protected static ?string $modelLabel = 'Movimiento de Stock';

    protected static ?string $pluralModelLabel = 'Movimientos de Stock';

    protected static ?string $slug = 'movimientos-stock';

    protected static bool $shouldRegisterNavigation = false; // Ocultar del menú

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'view' => ViewStockMovement::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Los movimientos se crean automáticamente
    }
}
