<div class="h-full flex">
    {{-- Diseño de 3 columnas usando Flexbox --}}
    
    {{-- SIDEBAR IZQUIERDO - CATEGORÍAS --}}
    <aside class="w-32 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col overflow-y-auto">
        <div class="p-4">
            <h2 class="text-gray-900 dark:text-white font-semibold text-sm mb-4">Categorías</h2>
            
            {{-- Botón para "Todas las categorías" --}}
            <div class="space-y-2">
                <button 
                    wire:click="selectCategory(null)"
                    class="w-full rounded-lg p-3 text-center cursor-pointer transition
                        {{ $selectedCategory === null ? 'bg-cyan-600 hover:bg-cyan-700' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                >
                    <div class="text-2xl mb-1">📋</div>
                    <span class="text-xs {{ $selectedCategory === null ? 'text-white' : 'text-gray-900 dark:text-white' }} block">Todas</span>
                </button>

                @forelse($categories as $category)
                <button 
                    wire:click="selectCategory('{{ $category->id }}')"
                    class="w-full rounded-lg p-3 text-center cursor-pointer transition
                        {{ $selectedCategory === $category->id ? 'bg-cyan-600 hover:bg-cyan-700' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                    style="{{ $selectedCategory === $category->id && $category->color ? 'background-color: ' . $category->color : '' }}"
                >
                    <div class="text-2xl mb-1">
                        {{-- Emoji o icono basado en el nombre --}}
                        @php
                            $emoji = match(true) {
                                str_contains(strtolower($category->name), 'bebida') => '🍹',
                                str_contains(strtolower($category->name), 'pizza') => '🍕',
                                str_contains(strtolower($category->name), 'burger') || str_contains(strtolower($category->name), 'hamburgues') => '🍔',
                                str_contains(strtolower($category->name), 'ensalada') => '🥗',
                                str_contains(strtolower($category->name), 'postre') => '🍰',
                                str_contains(strtolower($category->name), 'café') || str_contains(strtolower($category->name), 'cafe') => '☕',
                                str_contains(strtolower($category->name), 'carne') => '🥩',
                                str_contains(strtolower($category->name), 'pescado') => '🐟',
                                str_contains(strtolower($category->name), 'pasta') => '🍝',
                                str_contains(strtolower($category->name), 'vino') || str_contains(strtolower($category->name), 'cerveza') => '🍺',
                                default => '🍽️'
                            };
                        @endphp
                        {{ $emoji }}
                    </div>
                    <span class="text-xs {{ $selectedCategory === $category->id ? 'text-white' : 'text-gray-900 dark:text-white' }} block truncate">{{ $category->name }}</span>
                </button>
                @empty
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Sin categorías</span>
                </div>
                @endforelse
            </div>
        </div>
    </aside>

    {{-- CENTRO - PRODUCTOS --}}
    <main class="flex-1 bg-gray-100 dark:bg-gray-900 overflow-y-auto">
        <div class="p-6">
            {{-- Header con búsqueda --}}
            <div class="mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Buscar productos..." 
                            class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                        >
                    </div>
                    @if($searchTerm)
                    <button 
                        wire:click="$set('searchTerm', '')"
                        class="px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Grid de productos --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @forelse($products as $product)
                <div 
                    wire:click="addToCart('{{ $product->id }}')"
                    class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition relative
                           {{ !$product->track_stock || $product->stock_quantity > 0 ? 'cursor-pointer hover:ring-2 hover:ring-cyan-500' : 'opacity-50 cursor-not-allowed' }}"
                >
                    {{-- Nombre arriba --}}
                    <div class="p-3 pb-2">
                        <h3 class="text-gray-900 dark:text-white font-medium text-sm line-clamp-2 min-h-[2.5rem]">{{ $product->name }}</h3>
                    </div>

                    {{-- Imagen --}}
                    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center relative">
                        @if($product->image_path && \Storage::exists($product->image_path))
                            <img 
                                src="{{ \Storage::url($product->image_path) }}" 
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            {{-- Emoji placeholder según categoría --}}
                            <span class="text-5xl">
                                @php
                                    $categoryName = strtolower($product->category->name ?? '');
                                    $emoji = match(true) {
                                        str_contains($categoryName, 'bebida') => '🍹',
                                        str_contains($categoryName, 'pizza') => '🍕',
                                        str_contains($categoryName, 'burger') || str_contains($categoryName, 'hamburgues') => '🍔',
                                        str_contains($categoryName, 'ensalada') => '🥗',
                                        str_contains($categoryName, 'postre') => '🍰',
                                        str_contains($categoryName, 'café') || str_contains($categoryName, 'cafe') => '☕',
                                        str_contains($categoryName, 'carne') => '🥩',
                                        str_contains($categoryName, 'pescado') => '🐟',
                                        str_contains($categoryName, 'pasta') => '🍝',
                                        str_contains($categoryName, 'vino') || str_contains($categoryName, 'cerveza') => '🍺',
                                        default => '🍽️'
                                    };
                                @endphp
                                {{ $emoji }}
                            </span>
                        @endif

                        {{-- Indicador de stock con check --}}
                        @if($product->track_stock)
                            @if($product->stock_quantity > 0)
                                <div class="absolute top-2 right-2 bg-green-500 rounded-full p-1.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                {{-- Indicador de stock bajo --}}
                                @if($product->stock_quantity <= $product->low_stock_threshold)
                                    <div class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        ¡Bajo!
                                    </div>
                                @endif
                            @else
                                <div class="absolute top-2 right-2 bg-red-500 rounded-full p-1.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">SIN STOCK</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Precio --}}
                    <div class="p-3 pt-2">
                        <p class="text-green-400 font-semibold text-lg">
                            €{{ number_format($product->sale_price / 100, 2) }}
                        </p>
                        @if($product->track_stock)
                            <p class="text-gray-400 text-xs mt-1">
                                Stock: {{ $product->stock_quantity }}
                            </p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-sm">
                        @if($searchTerm)
                            No se encontraron productos para "{{ $searchTerm }}"
                        @elseif($selectedCategory)
                            No hay productos en esta categoría
                        @else
                            No hay productos disponibles
                        @endif
                    </p>
                </div>
                @endforelse
            </div>
        </div>
    </main>

    {{-- SIDEBAR DERECHO - TICKET --}}
    <aside class="w-96 bg-white dark:bg-gray-800 flex flex-col h-full border-l border-gray-200 dark:border-gray-700">
        {{-- Header del ticket --}}
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pedido Actual</h2>
                @if(count($cart) > 0)
                <button 
                    wire:click="clearCart"
                    wire:confirm="¿Estás seguro de limpiar todo el carrito?"
                    class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium"
                >
                    Limpiar
                </button>
                @endif
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Mesa: <span class="font-semibold text-gray-900 dark:text-white">#1</span>
            </div>
        </div>

        {{-- Lista de productos del carrito --}}
        <div class="flex-1 overflow-y-auto p-4">
            @if(empty($cart))
            <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500">
                <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <p class="text-sm">No hay productos en el carrito</p>
            </div>
            @else
            {{-- Productos del carrito --}}
            <div class="space-y-3">
                @foreach($cart as $item)
                <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                €{{ number_format($item['price'] / 100, 2) }}
                                @if(isset($item['tax_rate']) && $item['tax_rate'] > 0)
                                    <span class="ml-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-1.5 py-0.5 rounded">IVA {{ $item['tax_rate'] }}%</span>
                                @endif
                            </p>
                        </div>
                        <button 
                            wire:click="removeFromCart('{{ $item['id'] }}')"
                            class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button 
                                wire:click="decrementQuantity('{{ $item['id'] }}')"
                                class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded text-gray-900 dark:text-white font-bold"
                            >
                                -
                            </button>
                            <span class="w-12 text-center font-semibold text-gray-900 dark:text-white">{{ $item['quantity'] }}</span>
                            <button 
                                wire:click="incrementQuantity('{{ $item['id'] }}')"
                                class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded text-gray-900 dark:text-white font-bold"
                            >
                                +
                            </button>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                €{{ number_format(($item['price'] * $item['quantity']) / 100, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Resumen y botones de acción --}}
        <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900">
            {{-- Subtotales --}}
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span>€{{ number_format($this->subtotal / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>IVA:</span>
                    <span>€{{ number_format($this->tax / 100, 2) }}</span>
                </div>
                @if($tip > 0)
                <div class="flex justify-between text-sm text-green-600 dark:text-green-400">
                    <span>Propina:</span>
                    <span>€{{ number_format($tip / 100, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-300 dark:border-gray-600">
                    <span>Total:</span>
                    <span>€{{ number_format($this->total / 100, 2) }}</span>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="space-y-2">
                <button 
                    wire:click="openPaymentModal"
                    @if(empty($cart)) disabled @endif
                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold py-3 px-4 rounded-lg transition shadow-lg"
                >
                    Cobrar Pedido
                </button>
            </div>
        </div>
    </aside>

    {{-- MODAL DE PAGO --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closePaymentModal">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-lg m-4">
            {{-- Header del Modal --}}
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Procesar Pago</h3>
                    <button wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                
                {{-- Total en Grande --}}
                <div class="mt-6 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total a cobrar</div>
                    <div class="text-5xl font-bold text-gray-900 dark:text-white">
                        €{{ number_format($this->total / 100, 2) }}
                    </div>
                </div>
            </div>

            {{-- Cuerpo del Modal --}}
            <div class="p-6 space-y-6">
                @if(!$paymentMethod)
                    {{-- Selección de Método de Pago - Botones GRANDES --}}
                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            wire:click="selectPaymentMethod('cash')"
                            class="group p-8 border-2 border-gray-300 dark:border-gray-600 rounded-xl hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-200"
                        >
                            <div class="text-6xl mb-4">💵</div>
                            <div class="font-bold text-xl text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">
                                Efectivo
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Pago en caja
                            </div>
                        </button>

                        <button 
                            wire:click="selectPaymentMethod('card')"
                            class="group p-8 border-2 border-gray-300 dark:border-gray-600 rounded-xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200"
                        >
                            <div class="text-6xl mb-4">💳</div>
                            <div class="font-bold text-xl text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                Tarjeta
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Datáfono externo
                            </div>
                        </button>
                    </div>
                
                @elseif($paymentMethod === 'cash')
                    {{-- Pago en Efectivo - Cálculo de Cambio --}}
                    <div class="space-y-4">
                        <div class="text-center">
                            <div class="text-5xl mb-4">💵</div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pago en Efectivo</h4>
                        </div>

                        {{-- Input de dinero recibido --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dinero recibido del cliente
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg">€</span>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    wire:model.live="cashReceived"
                                    placeholder="0.00"
                                    class="w-full pl-10 pr-4 py-4 text-2xl font-bold border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:bg-gray-700 dark:text-white"
                                    autofocus
                                >
                            </div>
                        </div>

                        {{-- Mostrar cambio --}}
                        @if($cashReceived && $this->change >= 0)
                        <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-lg p-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Cambio a devolver</div>
                            <div class="text-4xl font-bold text-green-600 dark:text-green-400">
                                €{{ number_format($this->change / 100, 2) }}
                            </div>
                        </div>
                        @endif

                        {{-- Botones de acción --}}
                        <div class="flex gap-3 pt-4">
                            <button 
                                wire:click="closePaymentModal"
                                class="flex-1 px-6 py-4 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-semibold text-lg"
                            >
                                Cancelar
                            </button>
                            <button 
                                wire:click="processPayment"
                                class="flex-1 px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold text-lg shadow-lg"
                            >
                                ✓ Confirmar Cobro
                            </button>
                        </div>
                    </div>

                @elseif($paymentMethod === 'card')
                    {{-- Pago con Tarjeta - Datáfono Externo --}}
                    <div class="space-y-6">
                        <div class="text-center">
                            <div class="text-6xl mb-4">💳</div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pago con Tarjeta</h4>
                            <p class="text-gray-600 dark:text-gray-400">Procesar cobro en terminal físico</p>
                        </div>

                        {{-- Instrucciones --}}
                        <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-700 rounded-lg p-6">
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <div class="text-2xl">1️⃣</div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Introduce el importe</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            €{{ number_format($this->total / 100, 2) }} en el datáfono
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="text-2xl">2️⃣</div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Procesa el cobro</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Espera la confirmación en el terminal
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="text-2xl">3️⃣</div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Confirma en pantalla</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Pulsa "Confirmar Cobro" para cerrar el ticket
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex gap-3">
                            <button 
                                wire:click="closePaymentModal"
                                class="flex-1 px-6 py-4 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-semibold text-lg"
                            >
                                Cancelar
                            </button>
                            <button 
                                wire:click="processPayment"
                                class="flex-1 px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold text-lg shadow-lg"
                            >
                                ✓ Confirmar Cobro
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Script para abrir el ticket en nueva ventana --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-ticket', (event) => {
            const orderId = event.orderId;
            const ticketUrl = '{{ route("pos.ticket", ":orderId") }}'.replace(':orderId', orderId);
            window.open(ticketUrl, '_blank', 'width=800,height=600');
        });
    });
</script>
    </aside>
</div>
