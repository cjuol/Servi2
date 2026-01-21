# 🚀 Guía de Implementación del Sistema de Pagos TPV

## ✅ Implementación Completa

Se han implementado exitosamente los 3 módulos del sistema de pagos para el TPV:

### 1. **Backend - Lógica de Pedidos con Transacciones DB**
- ✅ Modelo `Order` actualizado con campos: `payment_method`, `ticket_number`, `stripe_payment_id`
- ✅ Métodos implementados en `OrderTerminal.php`:
  - `createPaymentIntent()`: Genera PaymentIntent de Stripe
  - `processPaymentCash()`: Procesa pagos en efectivo
  - `processPaymentCard()`: Procesa pagos con tarjeta
  - `completeOrder()`: Gestiona toda la lógica de DB con transacción atómica
  - `generateTicketNumber()`: Genera números únicos de ticket

### 2. **Integración Stripe**
- ✅ Frontend con Stripe Elements integrado en el modal de pago
- ✅ Confirmación de pago sin recargar la página
- ✅ Manejo de errores en tiempo real
- ✅ Guardado del `payment_intent_id` en la base de datos

### 3. **Generación de Tickets Térmicos (80mm)**
- ✅ Vista Blade optimizada para impresoras térmicas
- ✅ Auto-impresión al cargar la página
- ✅ Diseño profesional con toda la información del pedido
- ✅ Ruta protegida con autenticación

---

## 📋 Pasos para Completar la Instalación

### 1. **Instalar el SDK de Stripe**
```bash
composer require stripe/stripe-php
```

### 2. **Ejecutar la Migración**
```bash
php artisan migrate
```

### 3. **Configurar Variables de Entorno**

Añade estas variables a tu archivo `.env`:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_SECRET=sk_test_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

**Para obtener tus claves de Stripe:**
1. Accede a https://dashboard.stripe.com/
2. Ve a **Developers > API keys**
3. Copia la **Publishable key** (pk_test_...) y **Secret key** (sk_test_...)
4. Para producción, usa las claves que empiezan con `pk_live_` y `sk_live_`

### 4. **Actualizar config/services.php**

Añade la configuración de Stripe al archivo `config/services.php`:

```php
return [
    // ... otras configuraciones existentes

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];
```

### 5. **Limpiar Caché (Importante)**
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🎯 Cómo Funciona

### Flujo de Pago en Efectivo
1. Usuario añade productos al carrito
2. Presiona "Cobrar Pedido"
3. Selecciona "Efectivo"
4. Confirma el pago
5. **Backend crea:**
   - Pedido en DB con estado "completed"
   - Items del pedido
   - Actualiza el stock (decrement atómico)
   - Genera número de ticket único
6. Se abre automáticamente la ventana del ticket para imprimir

### Flujo de Pago con Tarjeta (Stripe)
1. Usuario añade productos al carrito
2. Presiona "Cobrar Pedido"
3. Selecciona "Tarjeta"
4. **Backend crea un PaymentIntent** y devuelve el `client_secret`
5. Se muestra el formulario de Stripe Elements
6. Usuario introduce los datos de la tarjeta
7. **JavaScript confirma el pago** con Stripe
8. Si el pago es exitoso:
   - JavaScript llama a `processPaymentCard()` con el `payment_intent_id`
   - **Backend crea el pedido** con toda la lógica transaccional
   - Se guarda el ID de pago de Stripe
9. Se abre automáticamente la ventana del ticket

### Transacciones Atómicas (DB::transaction)
El método `completeOrder()` garantiza que:
- **Todo se guarda o nada se guarda**
- Si falla cualquier paso (pedido, items, stock), se hace rollback completo
- Protección contra race conditions usando `decrement()` atómico
- Validación de stock negativo antes de confirmar

---

## 🧪 Testing Stripe (Modo Test)

Usa estas tarjetas de prueba:

| Número | Descripción |
|--------|-------------|
| `4242 4242 4242 4242` | Pago exitoso |
| `4000 0000 0000 9995` | Pago rechazado (fondos insuficientes) |
| `4000 0027 6000 3184` | Requiere autenticación 3D Secure |

- **Fecha de expiración:** Cualquier fecha futura (ej: 12/28)
- **CVV:** Cualquier 3 dígitos (ej: 123)
- **Código Postal:** Cualquier 5 dígitos

---

## 🖨️ Configuración de Impresora Térmica

### Impresoras ESC/POS (80mm)
El ticket está optimizado para impresoras térmicas estándar de 80mm.

**Recomendaciones:**
- Usa navegadores modernos (Chrome/Edge recomendados)
- En Chrome, ve a configuración de impresión y selecciona:
  - **Tamaño de papel:** Personalizado 80mm x auto
  - **Márgenes:** Ninguno
  - **Escala:** 100%

### Auto-impresión
El ticket se imprime automáticamente al abrir la ventana gracias al script:
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

## 🔒 Seguridad Implementada

✅ **Validación de Stock**: Verifica disponibilidad antes de guardar
✅ **Transacciones Atómicas**: Todo o nada con DB::transaction
✅ **Protección CSRF**: Implementado por Livewire
✅ **Autenticación Requerida**: Rutas protegidas con middleware auth
✅ **Inyección de Dependencias**: Sin variables hardcodeadas
✅ **Decrementos Atómicos**: Uso de `decrement()` para evitar race conditions
✅ **Validación de Pagos**: Solo guarda el pedido si Stripe confirma el éxito

---

## 📁 Archivos Creados/Modificados

### Creados:
- ✅ `resources/views/pos/ticket.blade.php` - Vista del ticket térmico
- ✅ `database/migrations/2026_01_21_120000_add_payment_fields_to_orders_table.php` - Migración

### Modificados:
- ✅ `app/Models/Order.php` - Añadidos campos de pago
- ✅ `app/Livewire/Pos/OrderTerminal.php` - Lógica completa de pagos
- ✅ `resources/views/livewire/pos/order-terminal.blade.php` - Modal y UI de Stripe
- ✅ `routes/web.php` - Ruta del ticket

---

## 🐛 Troubleshooting

### Error: "Stripe is not defined"
- Verifica que el script de Stripe se cargue: `<script src="https://js.stripe.com/v3/"></script>`
- Asegúrate de que el modal esté visible cuando se ejecuta el script

### Error: "Class 'Stripe\Stripe' not found"
- Ejecuta: `composer require stripe/stripe-php`

### Error: "No such PaymentIntent"
- Verifica que las claves de Stripe en `.env` sean correctas
- Asegúrate de usar claves del mismo entorno (test o live)

### El ticket no imprime automáticamente
- Verifica que tu navegador permita pop-ups desde tu dominio
- Comprueba que JavaScript esté habilitado

### Stock negativo después de venta
- Revisa que el campo `track_stock` esté correctamente configurado
- Verifica los seeders/datos de prueba

---

## 📞 Soporte

Para más información sobre Stripe:
- Documentación: https://stripe.com/docs
- Dashboard: https://dashboard.stripe.com/
- Testing: https://stripe.com/docs/testing

---

**¡El sistema está listo para usar!** 🎉

Recuerda ejecutar las migraciones y configurar las claves de Stripe antes de probar el sistema.
