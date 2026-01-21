<?php

/**
 * EJEMPLOS DE TESTING PARA LA REFACTORIZACIÓN DEL TPV
 * 
 * Este archivo contiene ejemplos de pruebas unitarias y de integración
 * para validar el nuevo flujo de ventas con trazabilidad completa.
 */

namespace Tests\Feature\Pos;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Livewire\Pos\OrderTerminal;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTerminalRefactoredTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'sale_price' => 1000, // 10.00€
            'stock_quantity' => 50,
            'track_stock' => true,
        ]);
    }

    /** @test */
    public function al_abrir_modal_de_pago_se_genera_ticket_inmediatamente(): void
    {
        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 2,
                    'track_stock' => true,
                    'stock_quantity' => 50,
                ]
            ])
            ->call('openPaymentModal')
            ->assertSet('showPaymentModal', true)
            ->assertNotNull('currentOrder');

        // Verificar que la orden se creó en la base de datos
        $this->assertDatabaseCount('orders', 1);
        
        $order = Order::first();
        $this->assertEquals(OrderStatus::OPEN, $order->status);
        $this->assertNull($order->payment_method);
        $this->assertNotNull($order->ticket_number);
        $this->assertEquals(2000, $order->total); // 2 * 1000
        
        // Verificar que los items se crearon
        $this->assertDatabaseCount('order_items', 1);
        $this->assertEquals(2, $order->items->first()->quantity);
        
        // Verificar que el stock NO se ha modificado todavía
        $this->assertEquals(50, $this->product->fresh()->stock_quantity);
        
        // Verificar que NO hay movimientos de stock todavía
        $this->assertDatabaseCount('stock_movements', 0);
    }

    /** @test */
    public function al_confirmar_pago_se_actualiza_orden_y_descuenta_stock(): void
    {
        $component = Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 3,
                    'track_stock' => true,
                    'stock_quantity' => 50,
                ]
            ])
            ->call('openPaymentModal')
            ->set('paymentMethod', 'cash')
            ->call('processPayment');

        // Verificar que la orden se actualizó
        $order = Order::first();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(PaymentMethod::CASH, $order->payment_method);
        
        // Verificar que el stock se decrementó
        $this->assertEquals(47, $this->product->fresh()->stock_quantity); // 50 - 3
        
        // Verificar que se creó el movimiento de stock con order_id
        $this->assertDatabaseCount('stock_movements', 1);
        
        $movement = StockMovement::first();
        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals($order->id, $movement->order_id); // ← NUEVA RELACIÓN
        $this->assertEquals(-3, $movement->quantity); // Negativo (salida)
        $this->assertEquals(StockMovement::TYPE_SALE, $movement->type);
        $this->assertStringContainsString($order->ticket_number, $movement->reason);
        
        // Verificar que el carrito y modal se limpiaron
        $component->assertSet('cart', []);
        $component->assertSet('showPaymentModal', false);
        $component->assertNull('currentOrder');
    }

    /** @test */
    public function al_cancelar_pago_se_elimina_la_orden(): void
    {
        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 2,
                    'track_stock' => true,
                    'stock_quantity' => 50,
                ]
            ])
            ->call('openPaymentModal')
            ->assertDatabaseCount('orders', 1)
            ->call('closePaymentModal') // Cierra el modal sin pagar
            ->assertSet('showPaymentModal', false)
            ->assertNull('currentOrder');

        // Verificar que la orden se eliminó
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        
        // Verificar que el stock NO se modificó
        $this->assertEquals(50, $this->product->fresh()->stock_quantity);
        
        // Verificar que NO hay movimientos de stock
        $this->assertDatabaseCount('stock_movements', 0);
    }

    /** @test */
    public function no_se_puede_vender_mas_stock_del_disponible(): void
    {
        $this->product->update(['stock_quantity' => 2]);

        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 5, // Intenta vender 5 pero solo hay 2
                    'track_stock' => true,
                    'stock_quantity' => 2,
                ]
            ])
            ->call('openPaymentModal')
            ->set('paymentMethod', 'card')
            ->call('processPayment');

        // Verificar que se lanzó una excepción y no se procesó el pago
        // El stock debe permanecer sin cambios
        $this->assertEquals(2, $this->product->fresh()->stock_quantity);
        
        // La orden debe seguir con estado OPEN (no se completó)
        $order = Order::first();
        $this->assertEquals(OrderStatus::OPEN, $order->status);
        $this->assertNull($order->payment_method);
    }

    /** @test */
    public function movimiento_de_stock_puede_acceder_a_su_orden(): void
    {
        // Realizar una venta
        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 1,
                    'track_stock' => true,
                    'stock_quantity' => 50,
                ]
            ])
            ->call('openPaymentModal')
            ->set('paymentMethod', 'cash')
            ->call('processPayment');

        $movement = StockMovement::first();
        $order = Order::first();

        // Probar la relación
        $this->assertNotNull($movement->order);
        $this->assertEquals($order->id, $movement->order->id);
        $this->assertEquals($order->ticket_number, $movement->order->ticket_number);
    }

    /** @test */
    public function orden_puede_acceder_a_sus_movimientos_de_stock(): void
    {
        // Venta con 2 productos diferentes
        $product2 = Product::factory()->create([
            'sale_price' => 500,
            'stock_quantity' => 100,
            'track_stock' => true,
        ]);

        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 2,
                    'track_stock' => true,
                    'stock_quantity' => 50,
                ],
                $product2->id => [
                    'id' => $product2->id,
                    'name' => $product2->name,
                    'price' => $product2->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 3,
                    'track_stock' => true,
                    'stock_quantity' => 100,
                ]
            ])
            ->call('openPaymentModal')
            ->set('paymentMethod', 'card')
            ->call('processPayment');

        $order = Order::first();

        // Probar la relación
        $this->assertCount(2, $order->stockMovements);
        
        $movements = $order->stockMovements;
        $this->assertTrue($movements->contains('product_id', $this->product->id));
        $this->assertTrue($movements->contains('product_id', $product2->id));
        
        // Verificar cantidades
        $movement1 = $movements->where('product_id', $this->product->id)->first();
        $this->assertEquals(-2, $movement1->quantity);
        
        $movement2 = $movements->where('product_id', $product2->id)->first();
        $this->assertEquals(-3, $movement2->quantity);
    }

    /** @test */
    public function productos_sin_track_stock_no_generan_movimientos(): void
    {
        $this->product->update(['track_stock' => false]);

        Livewire::test(OrderTerminal::class)
            ->set('cart', [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'tax_rate' => 10,
                    'quantity' => 5,
                    'track_stock' => false,
                    'stock_quantity' => 50,
                ]
            ])
            ->call('openPaymentModal')
            ->set('paymentMethod', 'cash')
            ->call('processPayment');

        // La orden se completa normalmente
        $this->assertDatabaseCount('orders', 1);
        $order = Order::first();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        
        // Pero NO se genera movimiento de stock
        $this->assertDatabaseCount('stock_movements', 0);
        
        // Y el stock no se modifica
        $this->assertEquals(50, $this->product->fresh()->stock_quantity);
    }
}
