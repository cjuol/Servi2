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
    
    // Nueva propiedad para el flujo refactorizado
    public ?Order $currentOrder = null;
    
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
        $this->currentOrder = null; // NUEVO: Limpiar orden actual
        
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
     * NUEVO: Ahora genera el ticket (pedido) inmediatamente
     */
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            return;
        }
        
        try {
            // Generar el ticket en la base de datos
            $this->generateTicket();
            
            // Abrir modal de pago
            $this->showPaymentModal = true;
            $this->paymentMethod = null;
            $this->cashReceived = null;
        } catch (\Exception $e) {
            \Log::error('Error generando ticket', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al generar el ticket: ' . $e->getMessage());
        }
    }

    /**
     * Cierra el modal de pago
     * NUEVO: Si existe una orden sin confirmar, se cancela
     */
    public function closePaymentModal()
    {
        $this->cancelPayment();
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
     * REFACTORIZADO: Ahora solo confirma el pago de la orden ya creada
     */
    public function processPayment()
    {
        // Validaciones
        if (!$this->currentOrder) {
            session()->flash('error', 'No hay orden pendiente');
            return;
        }

        if (!$this->paymentMethod) {
            session()->flash('error', 'Debe seleccionar un método de pago');
            return;
        }

        try {
            // Determinar el enum del método de pago
            $method = $this->paymentMethod === 'cash' ? PaymentMethod::CASH : PaymentMethod::CARD;
            
            // Finalizar el pago
            $this->finalizePayment($method);
            
            $this->lastOrderId = $this->currentOrder->id;
            
            \Log::info('Pago procesado exitosamente', ['order_id' => $this->currentOrder->id]);
            
            // Limpiar carrito y resetear estado COMPLETO
            $this->clearCart();
            $this->closePaymentModal();
            $this->reset(['searchTerm', 'selectedCategory', 'selectedTable']);
            
            session()->flash('success', 'Pago procesado correctamente');
            
            // Abrir el ticket en una nueva ventana
            $this->dispatch('open-ticket', orderId: $this->lastOrderId);
        } catch (\Exception $e) {
            \Log::error('Error procesando pago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * NUEVO: Genera el ticket (orden) en la base de datos con estado 'open'
     * No descuenta stock todavía
     */
    protected function generateTicket(): void
    {
        DB::transaction(function () {
            // 1. Generar número de ticket único
            $ticketNumber = $this->generateTicketNumber();

            // 2. Crear el pedido con estado 'open'
            $this->currentOrder = Order::create([
                'user_id' => Auth::id(),
                'status' => OrderStatus::OPEN,
                'payment_method' => null, // Se asignará al confirmar pago
                'total' => $this->total,
                'ticket_number' => $ticketNumber,
                'restaurant_table_id' => $this->selectedTable,
            ]);

            // 3. Crear los items del pedido (sin afectar stock todavía)
            foreach ($this->cart as $item) {
                $this->currentOrder->items()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            \Log::info('Ticket generado', [
                'order_id' => $this->currentOrder->id,
                'ticket_number' => $ticketNumber
            ]);
        });
    }

    /**
     * NUEVO: Finaliza el pago confirmado
     * Actualiza la orden a 'closed' y descuenta el stock
     */
    protected function finalizePayment(PaymentMethod $paymentMethod): void
    {
        if (!$this->currentOrder) {
            throw new \Exception('No hay orden para finalizar');
        }

        DB::transaction(function () use ($paymentMethod) {
            // 1. Actualizar el estado de la orden
            $this->currentOrder->update([
                'status' => OrderStatus::COMPLETED,
                'payment_method' => $paymentMethod,
            ]);

            // 2. Gestión de stock: Decrementar y registrar movimientos
            foreach ($this->currentOrder->items as $orderItem) {
                $product = Product::find($orderItem->product_id);
                
                if (!$product) {
                    throw new \Exception("Producto no encontrado: ID {$orderItem->product_id}");
                }

                // Si el producto trackea stock
                if ($product->track_stock) {
                    // Verificar stock disponible ANTES de decrementar
                    if ($product->stock_quantity < $orderItem->quantity) {
                        throw new \Exception("Stock insuficiente para: {$product->name}. Stock actual: {$product->stock_quantity}, requerido: {$orderItem->quantity}");
                    }

                    // Usar decrement atómico para evitar race conditions
                    $product->decrement('stock_quantity', $orderItem->quantity);
                    
                    // Crear el registro en StockMovement vinculando al pedido
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'order_id' => $this->currentOrder->id, // NUEVA RELACIÓN
                        'quantity' => -$orderItem->quantity, // Negativo porque es una salida
                        'type' => StockMovement::TYPE_SALE,
                        'reason' => "Venta TPV - Ticket #{$this->currentOrder->ticket_number}",
                    ]);
                }
            }

            \Log::info('Pago finalizado y stock actualizado', [
                'order_id' => $this->currentOrder->id
            ]);
        });
    }

    /**
     * NUEVO: Cancela el pago pendiente
     * Elimina la orden si el usuario cierra el modal sin confirmar
     */
    protected function cancelPayment(): void
    {
        if ($this->currentOrder) {
            try {
                $orderId = $this->currentOrder->id;
                
                // Hard delete de la orden y sus items (cascade)
                $this->currentOrder->delete();
                
                $this->currentOrder = null;
                
                \Log::info('Orden cancelada (modal cerrado sin pagar)', [
                    'order_id' => $orderId
                ]);
            } catch (\Exception $e) {
                \Log::error('Error cancelando orden', [
                    'message' => $e->getMessage()
                ]);
            }
        }
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
