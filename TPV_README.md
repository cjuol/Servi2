# 💳 Sistema de Pagos TPV - Quick Start

## 🚀 Instalación Rápida

### Opción 1: Script Automático (Recomendado)
```bash
chmod +x install.sh
./install.sh
```

### Opción 2: Manual

```bash
# 1. Instalar Stripe
composer require stripe/stripe-php

# 2. Ejecutar migraciones
php artisan migrate

# 3. Configurar .env
# Añade tus claves de Stripe:
STRIPE_KEY=pk_test_TU_CLAVE_AQUI
STRIPE_SECRET=sk_test_TU_SECRETO_AQUI

# 4. Limpiar caché
php artisan config:clear
php artisan cache:clear

# 5. Iniciar servidor
php artisan serve
```

## 🎯 Acceso al TPV

```
URL: http://localhost:8000/pos
```

## 🧪 Testing (Modo Desarrollo)

**Tarjetas de prueba de Stripe:**

| Tarjeta | Resultado |
|---------|-----------|
| `4242 4242 4242 4242` | ✅ Pago exitoso |
| `4000 0000 0000 9995` | ❌ Fondos insuficientes |
| `4000 0027 6000 3184` | 🔐 Requiere 3D Secure |

**Datos adicionales:**
- Fecha: Cualquier futura (ej: `12/28`)
- CVV: Cualquier 3 dígitos (ej: `123`)

## 📖 Documentación Completa

| Archivo | Descripción |
|---------|-------------|
| [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) | 📊 Resumen ejecutivo |
| [PAYMENT_SYSTEM_SETUP.md](PAYMENT_SYSTEM_SETUP.md) | 🛠️ Guía de instalación completa |
| [ARCHITECTURE.md](ARCHITECTURE.md) | 🏗️ Arquitectura técnica detallada |
| [EXAMPLES.md](EXAMPLES.md) | 💡 Ejemplos de uso paso a paso |

## ✨ Características

✅ **Pagos en Efectivo**
- Confirmación rápida
- Generación automática de tickets
- Control de stock integrado

✅ **Pagos con Tarjeta (Stripe)**
- Sin recarga de página
- Stripe Elements integrado
- Soporte 3D Secure
- Guardado de ID de transacción

✅ **Tickets Térmicos**
- Optimizado para impresoras 80mm
- Auto-impresión
- Diseño profesional
- Compatible con ESC/POS

✅ **Control de Stock**
- Decrementos atómicos
- Validación automática
- Protección contra race conditions
- Transacciones DB garantizadas

## 🔐 Obtener Claves de Stripe

1. Registrarte/Ingresar en: https://dashboard.stripe.com/
2. Ir a **Developers → API keys**
3. Copiar:
   - **Publishable key** → `STRIPE_KEY`
   - **Secret key** → `STRIPE_SECRET`
4. Añadir al archivo `.env`

## 🎨 Flujo de Uso

```
1. Añadir productos al carrito → Click en producto
2. Ver resumen → Sidebar derecho
3. Procesar pago → "Cobrar Pedido"
4. Seleccionar método → Efectivo o Tarjeta
5. Completar pago → Confirmar
6. Imprimir ticket → Auto-apertura
```

## 🆘 Problemas Comunes

### Error: "Stripe is not defined"
```bash
# Verificar que el modal esté visible
# El script de Stripe solo se carga cuando paymentMethod === 'card'
```

### Error: "Class 'Stripe\Stripe' not found"
```bash
composer require stripe/stripe-php
composer dump-autoload
```

### No imprime el ticket
```bash
# Verificar que el navegador permita pop-ups
# Chrome: Configuración → Privacidad → Ventanas emergentes
```

### Stock negativo
```bash
# Verificar campo track_stock del producto
# Solo productos con track_stock=true descuentan stock
```

## 🔄 Comandos Útiles

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar todo el caché
php artisan optimize:clear

# Ver migraciones pendientes
php artisan migrate:status

# Rollback de migración
php artisan migrate:rollback --step=1
```

## 📊 Base de Datos

**Tablas modificadas:**
- `orders` → Añadidos: `payment_method`, `ticket_number`, `stripe_payment_id`

**Para verificar:**
```sql
SELECT * FROM orders WHERE payment_method = 'card' LIMIT 5;
```

## 🎓 Soporte

- **Stripe Docs:** https://stripe.com/docs
- **Laravel 11:** https://laravel.com/docs/11.x
- **Livewire 5:** https://livewire.laravel.com/docs

## 📝 Checklist de Producción

Antes de ir a producción:

- [ ] Cambiar claves Stripe a modo live: `pk_live_...` y `sk_live_...`
- [ ] Configurar dominio real en `.env`
- [ ] Activar SSL/HTTPS
- [ ] Configurar backup automático de DB
- [ ] Revisar políticas de privacidad
- [ ] Configurar logs externos (Sentry, etc.)
- [ ] Probar impresoras físicas
- [ ] Capacitar personal de caja

## 🏆 Tecnologías

- Laravel 11
- Livewire 5
- Stripe PHP SDK
- Tailwind CSS
- PostgreSQL

---

**Desarrollado siguiendo las mejores prácticas de Laravel y arquitectura limpia.**

*Última actualización: 21 de enero de 2026*
