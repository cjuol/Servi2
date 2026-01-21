# 💡 Ejemplos de Uso del Sistema de Pagos TPV

## 🛒 Escenario 1: Venta en Efectivo

### Paso a Paso

**1. Usuario añade productos al carrito:**
```
Cliente pide:
- 2x Pizza Margarita (€12.50 c/u)
- 1x Coca Cola (€2.50)
- 1x Ensalada César (€8.90)

Total esperado: €36.40
```

**2. Cajero presiona "Cobrar Pedido"**
- Se abre el modal de pago
- Muestra el total: €36.40

**3. Selecciona "Efectivo"**
```php
// Backend ejecuta:
$this->paymentMethod = 'cash';
```

**4. Confirma el pago**
```php
// Backend ejecuta:
DB::transaction(function () {
    // 1. Genera ticket: 20260121-0023
    $ticketNumber = $this->generateTicketNumber();
    
    // 2. Crea el pedido
    $order = Order::create([
        'user_id' => Auth::id(),
        'status' => OrderStatus::COMPLETED,
        'payment_method' => PaymentMethod::CASH,
        'total' => 3640, // €36.40 en céntimos
        'ticket_number' => '20260121-0023',
    ]);
    
    // 3. Crea los items
    $order->items()->create([
        'product_id' => 'pizza-margarita-uuid',
        'quantity' => 2,
        'unit_price' => 1250,
        'tax_rate' => 10,
        'subtotal' => 2500,
    ]);
    // ... más items
    
    // 4. Actualiza stock
    Product::find('pizza-margarita-uuid')
        ->decrement('stock_quantity', 2);
    // ... más productos
});
```

**5. Se abre el ticket para imprimir**
```
┌─────────────────────────────┐
│      MI RESTAURANTE         │
│    Calle Ejemplo, 123       │
│   28080 Madrid, España      │
│   Tel: +34 912 345 678      │
│      NIF: B12345678         │
├─────────────────────────────┤
│ TICKET: #20260121-0023      │
│ FECHA: 21/01/2026 14:35     │
│ CAJERO: María García        │
├─────────────────────────────┤
│ CANT  PRODUCTO       IMPORTE│
│ 2     Pizza Margari  25,00€ │
│       (IVA 10%: 2,50€)      │
│ 1     Coca Cola       2,50€ │
│       (IVA 10%: 0,25€)      │
│ 1     Ensalada César  8,90€ │
│       (IVA 10%: 0,89€)      │
├─────────────────────────────┤
│ SUBTOTAL:            32,77€ │
│ IVA:                  3,64€ │
│ TOTAL:               36,40€ │
├─────────────────────────────┤
│ MÉTODO DE PAGO: Efectivo    │
│ ESTADO: Completado          │
├─────────────────────────────┤
│  *** GRACIAS POR SU VISITA  │
│ ¡Esperamos volver a verle!  │
└─────────────────────────────┘
```

---

## 💳 Escenario 2: Venta con Tarjeta (Stripe)

### Paso a Paso

**1. Usuario añade productos al carrito:**
```
Cliente pide:
- 1x Menú del día (€15.90)
- 1x Agua mineral (€1.50)

Total esperado: €17.40
```

**2. Cajero presiona "Cobrar Pedido"**
- Se abre el modal de pago
- Muestra el total: €17.40

**3. Selecciona "Tarjeta"**

**Backend crea PaymentIntent:**
```php
Stripe::setApiKey(config('services.stripe.secret'));

$paymentIntent = PaymentIntent::create([
    'amount' => 1740, // €17.40 en céntimos
    'currency' => 'eur',
    'automatic_payment_methods' => [
        'enabled' => true,
    ],
    'metadata' => [
        'user_id' => Auth::id(),
        'pos_terminal' => true,
    ],
]);

return $paymentIntent->client_secret;
// Devuelve: "pi_3ABC123_secret_xyz789"
```

**4. Frontend inicializa Stripe Elements:**
```javascript
const stripe = Stripe('pk_test_YOUR_KEY');
const elements = stripe.elements({ 
    clientSecret: 'pi_3ABC123_secret_xyz789' 
});

const cardElement = elements.create('payment');
cardElement.mount('#card-element');
```

**5. Cliente introduce datos de la tarjeta:**
```
Número: 4242 4242 4242 4242 (Visa de prueba)
Fecha: 12/28
CVV: 123
```

**6. Cliente presiona "Pagar €17.40"**

**JavaScript confirma el pago:**
```javascript
const { error, paymentIntent } = await stripe.confirmPayment({
    elements,
    redirect: 'if_required',
});

if (!error && paymentIntent.status === 'succeeded') {
    // Pago exitoso
    console.log('PaymentIntent ID:', paymentIntent.id);
    // Resultado: "pi_3ABC123def456"
    
    // Llamar a Livewire
    @this.call('processPaymentCard', paymentIntent.id);
}
```

**7. Backend guarda el pedido:**
```php
DB::transaction(function () use ($paymentIntentId) {
    // 1. Genera ticket
    $ticketNumber = '20260121-0024';
    
    // 2. Crea el pedido
    $order = Order::create([
        'user_id' => Auth::id(),
        'status' => OrderStatus::COMPLETED,
        'payment_method' => PaymentMethod::CARD,
        'total' => 1740,
        'ticket_number' => '20260121-0024',
        'stripe_payment_id' => 'pi_3ABC123def456', // ← Guardado
    ]);
    
    // 3-4. Items y stock...
});
```

**8. Se abre el ticket:**
```
┌─────────────────────────────┐
│      MI RESTAURANTE         │
├─────────────────────────────┤
│ TICKET: #20260121-0024      │
│ FECHA: 21/01/2026 18:45     │
├─────────────────────────────┤
│ 1     Menú del día   15,90€ │
│ 1     Agua mineral    1,50€ │
├─────────────────────────────┤
│ TOTAL:               17,40€ │
├─────────────────────────────┤
│ MÉTODO DE PAGO: Tarjeta     │
│ ID Stripe: pi_3ABC123def... │
│ ESTADO: Completado          │
└─────────────────────────────┘
```

---

## 🔄 Escenario 3: Control de Stock Automático

### Antes de la Venta

**Estado de la base de datos:**
```sql
SELECT name, stock_quantity FROM products 
WHERE name = 'Pizza Margarita';

-- Resultado:
-- name             | stock_quantity
-- Pizza Margarita  | 12
```

### Durante la Venta

**Cliente compra 3 pizzas:**
```php
// En completeOrder()
DB::transaction(function () {
    // Crear pedido e items...
    
    // Actualizar stock (operación atómica)
    Product::where('name', 'Pizza Margarita')
        ->decrement('stock_quantity', 3);
    
    // SQL generado:
    // UPDATE products 
    // SET stock_quantity = stock_quantity - 3
    // WHERE name = 'Pizza Margarita'
});
```

### Después de la Venta

**Estado actualizado:**
```sql
SELECT name, stock_quantity FROM products 
WHERE name = 'Pizza Margarita';

-- Resultado:
-- name             | stock_quantity
-- Pizza Margarita  | 9
```

### Si el Stock es Insuficiente

**Cliente intenta comprar 15 pizzas (pero solo hay 9):**
```php
DB::transaction(function () {
    $product = Product::where('name', 'Pizza Margarita')->first();
    
    // Intenta decrementar
    $product->decrement('stock_quantity', 15);
    
    // Verifica stock negativo
    $product->refresh();
    if ($product->stock_quantity < 0) {
        throw new \Exception("Stock insuficiente para: {$product->name}");
    }
});

// La transacción hace ROLLBACK
// No se guarda el pedido
// Stock permanece en 9
```

---

## 🎯 Escenario 4: Múltiples Terminales (Race Condition)

### Situación

**Dos cajeros intentan vender el mismo producto al mismo tiempo:**
- Terminal 1: Intenta vender las últimas 2 pizzas
- Terminal 2: Intenta vender las últimas 2 pizzas
- Stock actual: 2 unidades

### Sin Transacciones (❌ INCORRECTO)

```php
// Terminal 1:
$product = Product::find('pizza-uuid');
// Lee: stock_quantity = 2

// Terminal 2:
$product = Product::find('pizza-uuid');
// Lee: stock_quantity = 2 (¡también!)

// Terminal 1:
$product->stock_quantity -= 2;
$product->save();
// Escribe: stock_quantity = 0

// Terminal 2:
$product->stock_quantity -= 2;
$product->save();
// Escribe: stock_quantity = 0 (¡sobreescribe!)

// Resultado: Stock = 0, pero se vendieron 4 pizzas
// PROBLEMA: Overselling
```

### Con Decrementos Atómicos (✅ CORRECTO)

```php
// Terminal 1:
DB::transaction(function () {
    // UPDATE products SET stock_quantity = stock_quantity - 2
    Product::find('pizza-uuid')->decrement('stock_quantity', 2);
    // Stock: 2 → 0
});

// Terminal 2 (milisegundos después):
DB::transaction(function () {
    // UPDATE products SET stock_quantity = stock_quantity - 2
    Product::find('pizza-uuid')->decrement('stock_quantity', 2);
    // Stock: 0 → -2
    
    // Validación
    $product = Product::find('pizza-uuid');
    $product->refresh();
    if ($product->stock_quantity < 0) {
        throw new \Exception("Stock insuficiente");
    }
    // ROLLBACK automático
});

// Resultado: Terminal 1 vende correctamente
//            Terminal 2 recibe error de stock insuficiente
// Stock final: 0 unidades (correcto)
```

---

## 📊 Escenario 5: Consulta de Pedidos en Stripe Dashboard

### En el Dashboard de Stripe

**Buscar un pago:**
1. Ir a https://dashboard.stripe.com/payments
2. Buscar por: `pi_3ABC123def456`

**Información visible:**
```json
{
  "id": "pi_3ABC123def456",
  "amount": 1740,
  "currency": "eur",
  "status": "succeeded",
  "metadata": {
    "user_id": "550e8400-e29b-41d4-a716-446655440000",
    "pos_terminal": true
  },
  "created": 1705858500,
  "customer": null
}
```

### En tu Base de Datos

**Buscar el mismo pedido:**
```sql
SELECT 
    ticket_number,
    total,
    payment_method,
    stripe_payment_id,
    created_at
FROM orders
WHERE stripe_payment_id = 'pi_3ABC123def456';
```

**Resultado:**
```
ticket_number    | total | payment_method | stripe_payment_id   | created_at
20260121-0024    | 1740  | card          | pi_3ABC123def456    | 2026-01-21 18:45:00
```

**Cross-reference perfecto para auditorías** ✅

---

## 🧪 Escenario 6: Testing con Tarjetas de Stripe

### Pago Exitoso
```
Tarjeta: 4242 4242 4242 4242
Fecha: 12/28
CVV: 123

Resultado: ✅ succeeded
```

### Pago Rechazado (Fondos Insuficientes)
```
Tarjeta: 4000 0000 0000 9995
Fecha: 12/28
CVV: 123

Resultado: ❌ declined
Error: "Your card has insufficient funds."
```

### Pago con 3D Secure
```
Tarjeta: 4000 0027 6000 3184
Fecha: 12/28
CVV: 123

Resultado: 🔐 requires_action
Stripe muestra modal de autenticación
Usuario completa: ✅ succeeded
```

### Tarjeta Expirada
```
Tarjeta: 4000 0000 0000 0069
Fecha: 12/28
CVV: 123

Resultado: ❌ declined
Error: "Your card has expired."
```

---

## 📱 Escenario 7: Flujo Completo de un Día

### 08:00 - Apertura
```
Ticket: 20260121-0001
Total: €3.50 (Café + Tostada)
Método: Efectivo
```

### 14:30 - Hora punta de comidas
```
Ticket: 20260121-0042
Total: €48.90 (Mesa 5 - 4 personas)
Método: Tarjeta
Stripe ID: pi_3XYZ789...
```

### 16:00 - Stock bajo detectado
```
Producto: Cerveza Estrella
Stock actual: 3 unidades
Low threshold: 10 unidades
→ Sistema envía alerta (CheckLowStock listener)
```

### 21:45 - Última venta del día
```
Ticket: 20260121-0158
Total: €25.40
Método: Efectivo
```

### Consulta de cierre de caja
```sql
SELECT 
    payment_method,
    COUNT(*) as num_orders,
    SUM(total) as total_amount
FROM orders
WHERE DATE(created_at) = '2026-01-21'
GROUP BY payment_method;
```

**Resultado:**
```
payment_method | num_orders | total_amount
cash           | 95         | 245780  (€2,457.80)
card           | 63         | 189320  (€1,893.20)
TOTAL          | 158        | 435100  (€4,351.00)
```

---

## 🎓 Conclusiones

### ✅ Lo que has aprendido:
1. Transacciones atómicas con `DB::transaction`
2. Integración real de Stripe PaymentIntent
3. Operaciones atómicas de DB con `decrement()`
4. Manejo de race conditions
5. Generación de tickets térmicos
6. Arquitectura robusta para TPV

### 🚀 Próximos pasos:
- Implementar reportes de ventas
- Sistema de devoluciones
- Múltiples métodos de pago (PayPal, Bizum, etc.)
- Dashboard de analytics en tiempo real

---

**¡El sistema está listo para manejar ventas reales!** 🎉
