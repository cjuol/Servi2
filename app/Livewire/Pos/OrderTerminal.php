<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;

class OrderTerminal extends Component
{
    public $selectedCategory = null;
    public $cart = [];
    public $searchTerm = '';

    /**
     * Selecciona una categoría para filtrar productos
     */
    public function selectCategory($uuid)
    {
        // Si se selecciona la misma categoría, deseleccionarla (mostrar todas)
        $this->selectedCategory = $this->selectedCategory === $uuid ? null : $uuid;
    }

    /**
     * Agrega un producto al carrito
     */
    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_active) {
            return;
        }

        // Verificar stock si el producto lo trackea
        if ($product->track_stock && $product->stock_quantity <= 0) {
            return;
        }

        // Si el producto ya existe en el carrito, incrementar cantidad
        if (isset($this->cart[$productId])) {
            // Verificar que no se exceda el stock disponible
            if ($product->track_stock && $this->cart[$productId]['quantity'] >= $product->stock_quantity) {
                return;
            }
            $this->cart[$productId]['quantity']++;
        } else {
            // Agregar nuevo producto al carrito
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price,
                'quantity' => 1,
                'track_stock' => $product->track_stock,
                'stock_quantity' => $product->stock_quantity,
            ];
        }
    }

    /**
     * Incrementa la cantidad de un producto en el carrito
     */
    public function incrementQuantity($productId)
    {
        if (isset($this->cart[$productId])) {
            $product = Product::find($productId);
            
            // Verificar stock si es necesario
            if ($product->track_stock && $this->cart[$productId]['quantity'] >= $product->stock_quantity) {
                return;
            }
            
            $this->cart[$productId]['quantity']++;
        }
    }

    /**
     * Decrementa la cantidad de un producto en el carrito
     */
    public function decrementQuantity($productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']--;
            
            // Si la cantidad llega a 0, eliminar del carrito
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            }
        }
    }

    /**
     * Elimina un producto del carrito
     */
    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
    }

    /**
     * Limpia todo el carrito
     */
    public function clearCart()
    {
        $this->cart = [];
    }

    /**
     * Calcula el subtotal del carrito
     */
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    /**
     * Calcula el IVA (21%)
     */
    public function getTaxProperty()
    {
        return $this->subtotal * 0.21;
    }

    /**
     * Calcula el total
     */
    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax;
    }

    /**
     * Renderiza la vista del terminal de pedidos
     */
    #[Layout('components.layouts.pos')]
    public function render()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Cargar productos filtrados
        $productsQuery = Product::where('is_active', true)
            ->with('category');

        // Si hay búsqueda, ignorar el filtro de categoría
        if ($this->searchTerm) {
            // Búsqueda insensible a tildes en múltiples columnas
            $productsQuery->searchUnaccentedMultiple(
                ['name', 'sku', 'barcode'], 
                $this->searchTerm
            );
        } else {
            // Solo filtrar por categoría si NO hay búsqueda activa
            if ($this->selectedCategory) {
                $productsQuery->where('category_id', $this->selectedCategory);
            }
        }

        $products = $productsQuery->orderBy('name')->get();

        return view('livewire.pos.order-terminal', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
