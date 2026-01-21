# 🏗️ Arquitectura Técnica del Sistema de Pagos TPV

## 📐 Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (Livewire 5)                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  OrderTerminal.blade.php                                        │
│  ├─ Grid de Productos                                           │
│  ├─ Carrito de Compra                                           │
│  └─ Modal de Pago                                               │
│      ├─ Selección: Efectivo / Tarjeta                          │
│      ├─ Stripe Elements (Card Element)                         │
│      └─ JavaScript: stripe.confirmPayment()                    │
│                                                                 │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                   BACKEND (Laravel 11 + Livewire)               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  OrderTerminal.php (Componente Livewire)                        │
│  ├─ openPaymentModal()                                          │
│  ├─ selectPaymentMethod($method)                               │
│  │   └─ if (card) → createPaymentIntent()                      │
│  ├─ processPaymentCash()                                        │
│  │   └─ completeOrder(CASH)                                    │
│  └─ processPaymentCard($paymentIntentId)                       │
│      └─ completeOrder(CARD, $paymentIntentId)                  │
│                                                                 │
│  completeOrder() - Transacción DB Atómica:                      │
│  ├─ 1. Generar ticket_number                                   │
│  ├─ 2. Crear Order                                             │
│  ├─ 3. Crear OrderItems                                        │
│  ├─ 4. Decrementar Stock (atomic)                              │
│  └─ 5. Commit o Rollback                                       │
│                                                                 │
└────────────┬────────────────────┬───────────────────────────────┘
             │                    │
             ▼                    ▼
┌────────────────────┐   ┌─────────────────────────┐
│   DATABASE (PG)    │   │   STRIPE API            │
├────────────────────┤   ├─────────────────────────┤
│ orders             │   │ PaymentIntent.create()  │
│ ├─ id (uuid)       │   │ PaymentIntent.confirm() │
│ ├─ user_id         │   │                         │
│ ├─ total           │   └─────────────────────────┘
│ ├─ payment_method  │
│ ├─ ticket_number   │
│ └─ stripe_payment  │
│                    │
│ order_items        │
│ ├─ order_id        │
│ ├─ product_id      │
│ ├─ quantity        │
│ └─ subtotal        │
│                    │
│ products           │
│ └─ stock_quantity  │
│    (decremented)   │
└────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    TICKET GENERATION                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Route: /pos/ticket/{order}                                     │
│  View: ticket.blade.php (80mm thermal)                          │
│  ├─ CSS optimizado para impresoras ESC/POS                     │
│  ├─ Auto-print: window.print()                                 │
│  └─ Auto-close: window.close()                                 │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujos de Datos

### 1. Flujo de Pago en Efectivo

```
Usuario → Añade productos al carrito
   ↓
Usuario → Click "Cobrar Pedido"
   ↓
Frontend → Muestra Modal de Pago
   ↓
Usuario → Selecciona "Efectivo"
   ↓
Frontend → wire:click="selectPaymentMethod('cash')"
   ↓
Backend → $this->paymentMethod = 'cash'
   ↓
Usuario → Click "Confirmar Pago"
   ↓
Frontend → wire:click="processPaymentCash"
   ↓
Backend → completeOrder(PaymentMethod::CASH)
   ↓
DB::transaction {
   1. generateTicketNumber()
   2. Order::create([...])
   3. order->items()->create([...])
   4. Product::decrement('stock_quantity', qty)
}
   ↓
Frontend → dispatch('open-ticket', orderId)
   ↓
JavaScript → window.open('/pos/ticket/{order}')
   ↓
Navegador → Imprime ticket automáticamente
```

### 2. Flujo de Pago con Tarjeta (Stripe)

```
Usuario → Añade productos al carrito
   ↓
Usuario → Click "Cobrar Pedido"
   ↓
Frontend → Muestra Modal de Pago
   ↓
Usuario → Selecciona "Tarjeta"
   ↓
Frontend → wire:click="selectPaymentMethod('card')"
   ↓
Backend → createPaymentIntent()
   ↓
Stripe API → PaymentIntent::create([
                 'amount' => $total,
                 'currency' => 'eur'
              ])
   ↓
Backend → return $paymentIntent->client_secret
   ↓
Frontend → Recibe $stripeClientSecret
   ↓
Frontend → JavaScript inicializa Stripe Elements
   ↓
JavaScript → stripe.elements({ clientSecret })
   ↓
JavaScript → cardElement.mount('#card-element')
   ↓
Usuario → Introduce datos de tarjeta
   ↓
Usuario → Click "Pagar"
   ↓
JavaScript → stripe.confirmPayment({ elements })
   ↓
Stripe API → Procesa el pago
   ↓
Stripe API → Devuelve paymentIntent.status
   ↓
JavaScript → if (status === 'succeeded')
   ↓
Frontend → @this.call('processPaymentCard', paymentIntentId)
   ↓
Backend → completeOrder(PaymentMethod::CARD, $paymentIntentId)
   ↓
DB::transaction {
   1. generateTicketNumber()
   2. Order::create([
         'stripe_payment_id' => $paymentIntentId
      ])
   3. order->items()->create([...])
   4. Product::decrement('stock_quantity', qty)
}
   ↓
Frontend → dispatch('open-ticket', orderId)
   ↓
JavaScript → window.open('/pos/ticket/{order}')
   ↓
Navegador → Imprime ticket automáticamente
```

---

## 🛡️ Garantías de Integridad

### Transacciones Atómicas (ACID)

**Implementación en `completeOrder()`:**

```php
return DB::transaction(function () use ($paymentMethod, $stripePaymentId) {
    // Operación atómica - Todo o Nada
    
    // 1. Crear pedido
    $order = Order::create([...]);
    
    // 2. Crear items
    foreach ($this->cart as $item) {
        $order->items()->create([...]);
        
        // 3. Actualizar stock (atómico)
        Product::find($item['id'])
            ->decrement('stock_quantity', $item['quantity']);
    }
    
    return $order;
});
```

**Propiedades ACID garantizadas:**
- ✅ **Atomicidad**: Todo se guarda o nada se guarda
- ✅ **Consistencia**: El estado de la DB es siempre válido
- ✅ **Aislamiento**: Las transacciones no interfieren entre sí
- ✅ **Durabilidad**: Una vez confirmado, el dato persiste

### Race Conditions

**Problema:** Dos usuarios intentan comprar el último producto al mismo tiempo.

**Solución:** Uso de `decrement()` atómico de Eloquent:

```php
// ❌ NO USAR (Race condition)
$product->stock_quantity -= $quantity;
$product->save();

// ✅ USAR (Operación atómica)
$product->decrement('stock_quantity', $quantity);

// SQL generado:
// UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
```

### Validación de Stock Negativo

```php
$product->refresh();
if ($product->stock_quantity < 0) {
    throw new \Exception("Stock insuficiente para: {$product->name}");
}
```

---

## 🎨 Componentes del Frontend

### 1. Modal de Pago (Livewire)

**Estados:**
1. `showPaymentModal = false` → Modal oculto
2. `showPaymentModal = true && !paymentMethod` → Selección de método
3. `paymentMethod = 'cash'` → Confirmación de efectivo
4. `paymentMethod = 'card'` → Formulario Stripe

**Propiedades Reactivas:**
```php
public $showPaymentModal = false;
public $paymentMethod = null;
public $stripeClientSecret = null;
```

### 2. Stripe Elements (JavaScript)

**Inicialización:**
```javascript
const stripe = Stripe('pk_test_...');
const elements = stripe.elements({ clientSecret });
const cardElement = elements.create('payment');
cardElement.mount('#card-element');
```

**Confirmación de Pago:**
```javascript
const { error, paymentIntent } = await stripe.confirmPayment({
    elements,
    redirect: 'if_required',
});

if (paymentIntent.status === 'succeeded') {
    // Llamar a Livewire
    @this.call('processPaymentCard', paymentIntent.id);
}
```

---

## 🖨️ Sistema de Tickets

### Vista Blade Optimizada (80mm)

**Características:**
- Ancho fijo: `80mm`
- Fuente: `Courier New, monospace`
- `@page { size: 80mm auto; margin: 0; }`
- Estructura:
  1. **Header**: Logo, datos empresa, NIF
  2. **Info**: Ticket#, Fecha, Cajero, Mesa
  3. **Items**: Tabla de productos con cantidades e importes
  4. **Totales**: Subtotal, IVA, Total
  5. **Pago**: Método de pago, ID Stripe (si aplica)
  6. **Footer**: Mensaje de agradecimiento

### Auto-impresión

```javascript
window.onload = function() {
    setTimeout(function() {
        window.print();
        
        setTimeout(function() {
            window.close();
        }, 500);
    }, 250);
};
```

---

## 📊 Modelo de Datos

### Tabla: orders

```sql
CREATE TABLE orders (
    id UUID PRIMARY KEY,
    restaurant_table_id UUID NULLABLE,
    user_id UUID NOT NULL,
    status VARCHAR NOT NULL,
    payment_method VARCHAR NULLABLE,  -- 'cash' | 'card'
    ticket_number VARCHAR UNIQUE,     -- '20260121-0001'
    stripe_payment_id VARCHAR NULLABLE,
    total INTEGER NOT NULL,           -- Céntimos
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla: order_items

```sql
CREATE TABLE order_items (
    id UUID PRIMARY KEY,
    order_id UUID NOT NULL,
    product_id UUID NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price INTEGER NOT NULL,    -- Precio histórico
    tax_rate INTEGER NOT NULL,      -- IVA histórico
    subtotal INTEGER NOT NULL,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔐 Seguridad

### 1. Autenticación
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', OrderTerminal::class);
    Route::get('/pos/ticket/{order}', ...);
});
```

### 2. Validación de Entrada
- Livewire valida automáticamente los tipos de datos
- Stripe valida los datos de tarjeta en el frontend

### 3. Protección CSRF
- Automática en Livewire
- Token incluido en todas las peticiones

### 4. Secrets Management
```php
// ✅ Variables en .env
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],

// ❌ NUNCA hardcodear
'secret' => 'sk_test_...',
```

### 5. Auditoría
- Cada pedido guarda `user_id` (quién lo creó)
- `created_at` registra cuándo se creó
- `stripe_payment_id` permite rastrear en Stripe Dashboard

---

## 📈 Escalabilidad

### Optimizaciones Implementadas

1. **Queries Eficientes:**
   ```php
   Product::where('is_active', true)
       ->with('category')  // Eager loading
       ->get();
   ```

2. **Decrementos Atómicos:**
   ```php
   $product->decrement('stock_quantity', $quantity);
   ```

3. **Índices en DB:**
   ```php
   $table->index(['status', 'created_at']);
   ```

### Mejoras Futuras

- **Redis Cache**: Cachear categorías y productos activos
- **Queue Jobs**: Procesamiento asíncrono de tickets
- **Event Sourcing**: Registrar todos los cambios de stock
- **WebSockets**: Actualización en tiempo real entre terminales

---

## 🧪 Testing

### Test de Pago en Efectivo
```bash
# 1. Añadir productos al carrito
# 2. Click "Cobrar Pedido"
# 3. Seleccionar "Efectivo"
# 4. Confirmar
# 5. Verificar:
#    - Pedido creado en DB
#    - Stock decrementado
#    - Ticket generado
```

### Test de Pago con Tarjeta
```bash
# Tarjeta de prueba: 4242 4242 4242 4242
# Fecha: 12/28
# CVV: 123

# 1. Añadir productos al carrito
# 2. Click "Cobrar Pedido"
# 3. Seleccionar "Tarjeta"
# 4. Introducir datos
# 5. Verificar:
#    - PaymentIntent creado en Stripe
#    - Pago confirmado
#    - Pedido en DB con stripe_payment_id
#    - Stock decrementado
```

---

## 📚 Referencias

- **Laravel 11**: https://laravel.com/docs/11.x
- **Livewire 5**: https://livewire.laravel.com/docs
- **Stripe PHP**: https://stripe.com/docs/api/php
- **Stripe Elements**: https://stripe.com/docs/payments/elements
- **ESC/POS Printing**: https://en.wikipedia.org/wiki/ESC/P

---

**Diseñado con ❤️ siguiendo las mejores prácticas de Laravel y arquitectura limpia.**
