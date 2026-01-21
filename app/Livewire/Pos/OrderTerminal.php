<?php

namespace App\Livewire\Pos;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class OrderTerminal extends Component
{
    public $selectedCategory = null;
    public $cart = [];
    public $searchTerm = '';
    public $showPaymentModal = false;
    public $paymentMethod = null;
    public $lastOrderId = null;
    public $selectedTable = null;
    public $tip = 0;
    
    // Para cálculo de cambio en efectivo
    public $cashReceived = null;

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
                'tax_rate' => $product->tax_rate ?? 0,
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
        $this->cashReceived = null;
        
        // Forzar actualización del componente para limpiar totales
        $this->dispatch('$refresh');
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
     * Calcula el IVA según el tax_rate de cada producto
     */
    public function getTaxProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            $taxRate = ($item['tax_rate'] ?? 0) / 100;
            $subtotalItem = $item['price'] * $item['quantity'];
            return $subtotalItem * $taxRate;
        });
    }

    /**
     * Calcula el total
     */
    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax;
    }
    
    /**
     * Computed property para calcular el cambio
     */
    public function getChangeProperty()
    {
        if (!$this->cashReceived) {
            return 0;
        }
        
        $received = (int) ($this->cashReceived * 100); // Convertir a céntimos
        return max(0, $received - $this->total);
    }

    /**
     * Abre el modal de pago
     */
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            return;
        }
        
        $this->showPaymentModal = true;
        $this->paymentMethod = null;
        $this->cashReceived = null;
    }

    /**
     * Cierra el modal de pago
     */
    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentMethod = null;
        $this->cashReceived = null;
    }

    /**
     * Selecciona el método de pago
     */
    public function selectPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        
        // Para efectivo, resetear el input de dinero recibido
        if ($method === 'cash') {
            $this->cashReceived = null;
        }
    }

    /**
     * Procesa el pago (unificado para efectivo y tarjeta)
     */
    public function processPayment()
    {
        // Validaciones
        if (empty($this->cart)) {
            session()->flash('error', 'El carrito está vacío');
            return;
        }

        if (!$this->paymentMethod) {
            session()->flash('error', 'Debe seleccionar un método de pago');
            return;
        }

        try {
            // Log temporal para debug
            \Log::info('Procesando pago', [
                'payment_method' => $this->paymentMethod,
                'cart_items' => count($this->cart),
                'total' => $this->total
            ]);

            // Determinar el enum del método de pago
            $method = $this->paymentMethod === 'cash' ? PaymentMethod::CASH : PaymentMethod::CARD;
            
            // Completar la orden
            $order = $this->completeOrder($method);
            $this->lastOrderId = $order->id;
            
            \Log::info('Orden creada exitosamente', ['order_id' => $order->id]);
            
            // Limpiar carrito y resetear estado COMPLETO
            $this->clearCart();
            $this->closePaymentModal();
            $this->reset(['searchTerm', 'selectedCategory', 'selectedTable']);
            
            session()->flash('success', 'Pago procesado correctamente');
            
            // Abrir el ticket en una nueva ventana
            $this->dispatch('open-ticket', orderId: $order->id);
        } catch (\Exception $e) {
            \Log::error('Error procesando pago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Completa el pedido con transacción DB
     * Garantiza atomicidad: si algo falla, se hace rollback completo
     */
    protected function completeOrder(PaymentMethod $paymentMethod): Order
    {
        return DB::transaction(function () use ($paymentMethod) {
            // 1. Generar número de ticket único
            $ticketNumber = $this->generateTicketNumber();

            // 2. Crear el pedido
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => OrderStatus::COMPLETED,
                'payment_method' => $paymentMethod,
                'total' => $this->total,
                'ticket_number' => $ticketNumber,
            ]);

            // 3. Crear los items del pedido, actualizar stock y generar movimientos
            foreach ($this->cart as $item) {
                // Crear el item del pedido
                $order->items()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Obtener el producto
                $product = Product::find($item['id']);
                
                if (!$product) {
                    throw new \Exception("Producto no encontrado: ID {$item['id']}");
                }

                // Si el producto trackea stock
                if ($product->track_stock) {
                    // Verificar stock disponible ANTES de decrementar
                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para: {$product->name}. Stock actual: {$product->stock_quantity}, requerido: {$item['quantity']}");
                    }

                    // Usar decrement atómico para evitar race conditions
                    $product->decrement('stock_quantity', $item['quantity']);
                    
                    // Crear el registro en StockMovement (movimiento de salida por venta)
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'quantity' => -$item['quantity'], // Negativo porque es una salida
                        'type' => StockMovement::TYPE_SALE,
                        'reason' => "Venta TPV - Ticket #{$ticketNumber}",
                    ]);
                }
            }

            return $order;
        });
    }

    /**
     * Genera un número de ticket único
     */
    protected function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
        
        return $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
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
