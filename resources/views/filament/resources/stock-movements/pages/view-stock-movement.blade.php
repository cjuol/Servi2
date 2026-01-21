<x-filament-panels::page>
    {{-- Información del movimiento de stock --}}
    <div class="space-y-6">
        {{-- Detalles del movimiento --}}
        {{ $this->form }}

        {{-- Información del Ticket/Pedido asociado --}}
        @if($this->record->order_id && $this->record->order)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        📄 Información del Pedido
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Número de Ticket</dt>
                            <dd class="mt-2 text-lg text-gray-900 dark:text-white font-mono font-semibold">{{ $this->record->order->ticket_number }}</dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</dt>
                            <dd class="mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $this->record->order->status->value === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $this->record->order->status->value === 'open' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $this->record->order->status->value === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                    {{ ucfirst($this->record->order->status->value) }}
                                </span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Método de Pago</dt>
                            <dd class="mt-2 text-lg text-gray-900 dark:text-white font-medium">{{ $this->record->order->payment_method ? ucfirst($this->record->order->payment_method->value) : '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</dt>
                            <dd class="mt-2 text-lg text-gray-900 dark:text-white font-bold">{{ number_format($this->record->order->total / 100, 2) }} €</dd>
                        </div>
                        @if($this->record->order->restaurantTable)
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Mesa</dt>
                            <dd class="mt-2 text-lg text-gray-900 dark:text-white font-medium">{{ $this->record->order->restaurantTable->name }}</dd>
                        </div>
                        @endif
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fecha y Hora</dt>
                            <dd class="mt-2 text-lg text-gray-900 dark:text-white font-medium">{{ $this->record->order->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </div>

                    {{-- Items del pedido --}}
                    @if($this->record->order->items->count() > 0)
                        <div class="mt-8">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 uppercase tracking-wide">Productos del Pedido</h4>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Precio Unit.</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($this->record->order->items as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $item->product->name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 font-semibold">
                                                        {{ $item->quantity }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($item->unit_price / 100, 2) }} €</td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-semibold">{{ number_format($item->subtotal / 100, 2) }} €</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">TOTAL:</td>
                                            <td class="px-4 py-3 text-right text-lg font-bold text-primary-600 dark:text-primary-400">{{ number_format($this->record->order->total / 100, 2) }} €</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
