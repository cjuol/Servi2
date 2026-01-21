# Refactorización del Sistema TPV - Resumen de Implementación

## 📋 Cambios Estructurales Implementados

### PASO 1: Migración de Base de Datos ✅

**Archivo creado:** `database/migrations/2026_01_21_150000_refactor_orders_and_stock_movements.php`

#### Cambios aplicados:
1. **Tabla `orders`:**
   - ❌ **Eliminado:** Columna `stripe_payment_id` (ya no se utiliza)

2. **Tabla `stock_movements`:**
   - ✅ **Añadido:** Columna `order_id` (UUID, nullable)
   - ✅ **Añadido:** Foreign key hacia `orders.id` con `nullOnDelete`
   - ✅ **Añadido:** Índice en `order_id` para consultas rápidas

#### Ejecutar migración:
```bash
# Opción 1: Con Docker
docker compose -f enviroment/docker-compose.yml exec web php artisan migrate

# Opción 2: Directamente (si PHP está instalado)
php artisan migrate
```

---

### PASO 2: Actualización de Modelos ✅

#### `app/Models/Order.php`
- ✅ **Eliminado:** `stripe_payment_id` del array `$fillable`
- ✅ **Añadido:** Relación `stockMovements()` → `HasMany`

#### `app/Models/StockMovement.php`
- ✅ **Añadido:** `order_id` al array `$fillable`
- ✅ **Añadido:** Relación `order()` → `BelongsTo`

---

### PASO 3: Refactorización del TPV (`app/Livewire/Pos/OrderTerminal.php`) ✅

#### Nueva Propiedad:
```php
public ?Order $currentOrder = null;
```

#### Nuevo Flujo de Venta:

##### 1️⃣ `openPaymentModal()` (Al pulsar "Cobrar")
- **Antes:** Solo abría el modal
- **Ahora:**
  1. Llama a `generateTicket()` para crear la orden en DB
  2. Abre el modal de pago

##### 2️⃣ `generateTicket()` **[NUEVO MÉTODO PRIVADO]**
```php
protected function generateTicket(): void
```
- **Responsabilidad:** Crear el registro de la orden inmediatamente
- **Estado inicial:** `OrderStatus::OPEN`
- **Acciones:**
  - Genera `ticket_number` único
  - Crea `Order` con `payment_method = null`
  - Crea todos los `OrderItem` asociados
  - **NO descuenta stock todavía**
  - Guarda la orden en `$this->currentOrder`

##### 3️⃣ `finalizePayment($method)` **[NUEVO MÉTODO PRIVADO]**
```php
protected function finalizePayment(PaymentMethod $paymentMethod): void
```
- **Responsabilidad:** Confirmar el pago y actualizar stock
- **Acciones:**
  1. Actualiza la orden:
     - `status` → `OrderStatus::COMPLETED`
     - `payment_method` → `CASH` o `CARD`
  2. **Gestión de Stock (con transacción DB):**
     - Valida stock disponible
     - Decrementa `stock_quantity` en `products` (atómico)
     - Crea `StockMovement` vinculando **`order_id`** ← **NUEVA RELACIÓN**
     - `quantity` → negativo (salida)
     - `type` → `TYPE_SALE`
     - `reason` → "Venta TPV - Ticket #XXX"

##### 4️⃣ `cancelPayment()` **[NUEVO MÉTODO PRIVADO]**
```php
protected function cancelPayment(): void
```
- **Responsabilidad:** Limpiar orden si el usuario cierra el modal sin pagar
- **Acción:** Hard delete de la orden (cascade elimina items automáticamente)
- **Cuándo se ejecuta:** Al cerrar el modal de pago (`closePaymentModal()`)

##### 5️⃣ `processPayment()` **[REFACTORIZADO]**
- **Antes:** Creaba toda la orden y actualizaba stock
- **Ahora:** Solo llama a `finalizePayment()` con el método de pago seleccionado

##### 6️⃣ `clearCart()` **[ACTUALIZADO]**
- **Añadido:** `$this->currentOrder = null;` para limpiar referencia

---

### PASO 4: Recurso Filament para Trazabilidad ✅

#### Archivos creados:

##### 1. **Resource Principal**
📄 `app/Filament/Resources/StockMovements/StockMovementResource.php`
- **Navegación:** "Movimientos de Stock" en grupo "Gestión de Inventario"
- **Icono:** `heroicon-o-arrows-right-left`
- **Orden:** Prioridad 4
- **Creación deshabilitada:** `canCreate() → false` (se generan automáticamente)

##### 2. **Tabla con Acción "Ver Ticket"**
📄 `app/Filament/Resources/StockMovements/Tables/StockMovementsTable.php`

**Columnas:**
- ✅ Fecha/Hora
- ✅ Producto
- ✅ Tipo (badge con colores)
- ✅ Cantidad (con prefijo +/-)
- ✅ Usuario
- ✅ Motivo
- ✅ **Ticket** (enlace directo al ticket PDF)

**Filtros:**
- ✅ Por tipo de movimiento
- ✅ Por producto (searchable)

**Acción "Ver Ticket":**
```php
Action::make('view_ticket')
    ->label('Ver Ticket')
    ->icon('heroicon-o-eye')
    ->visible(fn (StockMovement $record): bool => $record->order_id !== null)
    ->modalContent(function (StockMovement $record): HtmlString {
        $order = Order::with(['items.product', 'user'])->find($record->order_id);
        return new HtmlString(view('pos.ticket', ['order' => $order])->render());
    })
```

**Características:**
- 👁️ Solo visible si el movimiento tiene `order_id`
- 📄 Renderiza la vista completa del ticket en un modal
- 🎨 Modal con ancho medio (`md`)
- ✅ Botón "Cerrar" (sin submit)

##### 3. **Página de Listado**
📄 `app/Filament/Resources/StockMovements/Pages/ListStockMovements.php`

##### 4. **Página de Vista Detalle**
📄 `app/Filament/Resources/StockMovements/Pages/ViewStockMovement.php`
- **Secciones:**
  1. Información del Movimiento
  2. Trazabilidad (Ticket + Albarán asociados)
- **Acción en header:** "Ver Ticket Original" (abre en nueva pestaña)

---

### PASO 5: Actualización del Esquema DBML ✅

**Archivo actualizado:** `database.dbml`

**Cambios:**
- ❌ Eliminado: `stripe_payment_id` de `orders`
- ✅ Añadido: `order_id` en `stock_movements` con foreign key y nota
- ✅ Añadido: Índice `order_id` en `stock_movements`

---

## 🔄 Flujo Completo de Venta (Nuevo)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Usuario agrega productos al carrito                         │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. Pulsa "Cobrar"                                               │
│    → openPaymentModal()                                         │
│      → generateTicket()                                         │
│         • Crea Order (status: OPEN, payment_method: null)       │
│         • Crea OrderItems                                       │
│         • Genera ticket_number                                  │
│         • Stock SIN MODIFICAR todavía                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. Se abre el modal de pago                                     │
│    • $currentOrder almacena la orden pendiente                  │
└─────────────────────────────────────────────────────────────────┘
                            ↓
           ┌────────────────┴────────────────┐
           ↓                                 ↓
┌──────────────────────┐        ┌──────────────────────┐
│ 4a. Confirma Pago    │        │ 4b. Cierra Modal     │
│ → processPayment()   │        │ → closePaymentModal()│
│   → finalizePayment()│        │   → cancelPayment()  │
│     • Order.status   │        │     • DELETE Order   │
│       → COMPLETED    │        │     • Items en       │
│     • payment_method │        │       cascade        │
│       → CASH/CARD    │        └──────────────────────┘
│     • Decrementa     │
│       stock_quantity │
│     • Crea           │
│       StockMovement  │
│       con order_id   │
│     • Imprime ticket │
└──────────────────────┘
```

---

## 🎯 Ventajas del Nuevo Flujo

### 1. **Trazabilidad Completa**
- ✅ Cada venta tiene su registro único en `orders`
- ✅ Cada movimiento de stock apunta al pedido que lo originó (`order_id`)
- ✅ Desde Filament puedes ver el ticket original que causó una bajada de stock

### 2. **Integridad de Datos**
- ✅ El ticket se crea **antes** de descontar stock
- ✅ Si el pago falla/cancela, la orden se elimina (no queda basura en DB)
- ✅ Transacciones DB garantizan atomicidad

### 3. **Auditoría y Reportes**
- ✅ Histórico completo de ventas en `orders`
- ✅ Kardex (stock_movements) vinculado a cada venta
- ✅ Fácil identificar qué ticket causó un movimiento

### 4. **UX Mejorada**
- ✅ El ticket se genera inmediatamente (el usuario ve el número)
- ✅ Modal de pago muestra información real de la orden
- ✅ Cancelación limpia sin efectos secundarios

---

## 📝 Notas Importantes

### Migración
- ⚠️ **Backup recomendado** antes de ejecutar la migración
- ⚠️ La columna `stripe_payment_id` se eliminará permanentemente
- ✅ La migración es reversible (`down()` restaura el estado anterior)

### Compatibilidad
- ✅ El método antiguo `completeOrder()` se mantiene como `@deprecated` por compatibilidad
- ✅ Tests existentes pueden necesitar actualización para el nuevo flujo

### Testing Recomendado
```bash
# 1. Probar flujo completo de venta
# 2. Probar cancelación de pago
# 3. Verificar que stock_movements tiene order_id
# 4. Probar acción "Ver Ticket" en Filament
```

---

## ✅ Checklist de Implementación

- [x] Crear migración `2026_01_21_150000_refactor_orders_and_stock_movements.php`
- [x] Actualizar modelo `Order` (eliminar stripe_payment_id, añadir relación)
- [x] Actualizar modelo `StockMovement` (añadir order_id, añadir relación)
- [x] Refactorizar `OrderTerminal.php` con nuevo flujo
- [x] Crear `StockMovementResource` completo en Filament
- [x] Actualizar `database.dbml`
- [ ] **Ejecutar migración en el servidor**
- [ ] Probar flujo de venta completo
- [ ] Verificar acción "Ver Ticket" en Filament
- [ ] Actualizar tests (si aplica)

---

## 🚀 Comandos de Despliegue

```bash
# 1. Aplicar migración
docker compose -f enviroment/docker-compose.yml exec web php artisan migrate

# 2. Limpiar caché de Filament (para registrar nuevo recurso)
docker compose -f enviroment/docker-compose.yml exec web php artisan filament:cache-components

# 3. Limpiar caché general
docker compose -f enviroment/docker-compose.yml exec web php artisan optimize:clear

# 4. Verificar que todo funciona
docker compose -f enviroment/docker-compose.yml exec web php artisan about
```

---

## 📞 Soporte

Si encuentras algún problema durante la implementación:
1. Verifica los logs de Laravel: `storage/logs/laravel.log`
2. Revisa los logs del navegador (consola y red)
3. Ejecuta `php artisan route:list` para verificar rutas
4. Ejecuta `php artisan migrate:status` para ver el estado de migraciones

---

**Implementación completada por:** Arquitecto de Software Senior especializado en Laravel 11, Livewire 3 y PostgreSQL
**Fecha:** 2026-01-21
**Versión:** 1.0.0
