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
                            <p class="text-sm text-gray-600 dark:text-gray-400">€{{ number_format($item['price'] / 100, 2) }}</p>
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
                    <span>IVA (21%):</span>
                    <span>€{{ number_format($this->tax / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-300 dark:border-gray-600">
                    <span>Total:</span>
                    <span>€{{ number_format($this->total / 100, 2) }}</span>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="space-y-2">
                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition shadow-lg">
                    Cobrar Pedido
                </button>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition shadow-lg">
                    Guardar Pedido
                </button>
                <button class="w-full bg-gray-600 hover:bg-gray-500 text-white font-semibold py-3 px-4 rounded-lg transition shadow-lg">
                    Cancelar
                </button>
            </div>
        </div>
    </aside>
</div>
