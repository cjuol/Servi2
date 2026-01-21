<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewStockMovement extends ViewRecord
{
    protected static string $resource = StockMovementResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Movimiento de Stock';
    }

    public function getView(): string
    {
        return 'filament.resources.stock-movements.pages.view-stock-movement';
    }

    protected function resolveRecord($key): Model
    {
        return parent::resolveRecord($key)->load(['order.items.product', 'product', 'user', 'deliveryNote']);
    }

    public function getTicketUrl(): ?string
    {
        if (!$this->record->order_id) {
            return null;
        }
        
        return route('pos.ticket', ['order' => $this->record->order_id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_ticket')
                ->label('Ver Ticket Original')
                ->icon('heroicon-o-ticket')
                ->color('primary')
                ->visible(fn (): bool => $this->record->order_id !== null)
                ->modalContent(fn () => view('filament.resources.stock-movements.components.ticket-modal', [
                    'ticketUrl' => $this->getTicketUrl(),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalWidth('xl')
                ->action(fn () => null)
                ->requiresConfirmation(false),
        ];
    }
}
