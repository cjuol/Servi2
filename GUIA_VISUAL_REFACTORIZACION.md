# 🎯 Refactorización TPV - Vista Rápida

## 📊 Resumen de Cambios

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Flujo de Venta** | Pago → Crear todo | Ticket → Pago → Confirmar |
| **Trazabilidad** | ❌ Sin vínculo Order↔Stock | ✅ `stock_movements.order_id` |
| **Cancelación** | ⚠️ No contemplada | ✅ Hard delete de orden |
| **Stripe** | ✅ `stripe_payment_id` en DB | ❌ Eliminado (no usado) |
| **Auditoría Filament** | ⚠️ Solo widget | ✅ Recurso completo + "Ver Ticket" |

---

## 🔄 Flujo Visual del Nuevo Sistema

```
                    ┌─────────────────────────────┐
                    │  Usuario en Terminal TPV    │
                    │  Agrega productos al 🛒     │
                    └──────────────┬──────────────┘
                                   │
                                   ↓
                    ┌─────────────────────────────┐
                    │   Presiona "COBRAR" 💰      │
                    └──────────────┬──────────────┘
                                   │
                                   ↓
        ┌──────────────────────────────────────────────────┐
        │  PASO 1: generateTicket()                        │
        │  ──────────────────────────                      │
        │  • Crea Order (status: OPEN)                     │
        │  • Genera ticket_number                          │
        │  • Crea OrderItems                               │
        │  • Stock SIN TOCAR                              │
        │  • Guarda en $currentOrder                       │
        └──────────────────┬───────────────────────────────┘
                           │
                           ↓
        ┌──────────────────────────────────────────────────┐
        │          🖥️  MODAL DE PAGO                       │
        │                                                   │
        │  ┌─────────────┐    ┌─────────────┐             │
        │  │   💵 CASH   │    │   💳 CARD   │             │
        │  └─────────────┘    └─────────────┘             │
        │                                                   │
        │  Ticket: #20260121-0042 ✅                       │
        │  Total: 25.50€                                   │
        └─────────────┬─────────────────┬──────────────────┘
                      │                 │
           ┌──────────┘                 └──────────┐
           │                                       │
           ↓                                       ↓
  ┌────────────────────┐              ┌──────────────────────┐
  │  CONFIRMAR PAGO ✅ │              │  CANCELAR/CERRAR ❌  │
  │  processPayment()  │              │  closePaymentModal() │
  └────────┬───────────┘              └──────────┬───────────┘
           │                                     │
           ↓                                     ↓
  ┌──────────────────────────────┐    ┌─────────────────────┐
  │ finalizePayment()            │    │ cancelPayment()     │
  │ ─────────────────            │    │ ───────────────     │
  │ 1. Update Order:             │    │ • DELETE Order      │
  │    - status → COMPLETED      │    │ • Items cascade     │
  │    - payment_method → CASH   │    │ • Stock intacto     │
  │                              │    │ • $currentOrder=null│
  │ 2. POR CADA ITEM:            │    └─────────────────────┘
  │    - product.decrement()     │
  │    - StockMovement::create() │
  │      ↳ order_id ← ORDER ✅   │
  │      ↳ quantity = -X         │
  │      ↳ type = 'sale'         │
  │                              │
  │ 3. Imprimir ticket 🖨️       │
  │ 4. Limpiar carrito           │
  └──────────────────────────────┘
```

---

## 🗃️ Estructura de Base de Datos

### TABLA: `orders`
```sql
┌────────────────────┬──────────┬─────────────┐
│ Campo              │ Tipo     │ Cambio      │
├────────────────────┼──────────┼─────────────┤
│ id                 │ UUID     │             │
│ user_id            │ UUID     │             │
│ status             │ VARCHAR  │             │
│ payment_method     │ VARCHAR  │             │
│ ticket_number      │ VARCHAR  │             │
│ stripe_payment_id  │ VARCHAR  │ ❌ ELIMINADO│
│ total              │ INTEGER  │             │
│ tip                │ INTEGER  │             │
└────────────────────┴──────────┴─────────────┘
```

### TABLA: `stock_movements`
```sql
┌────────────────────┬──────────┬─────────────┐
│ Campo              │ Tipo     │ Cambio      │
├────────────────────┼──────────┼─────────────┤
│ id                 │ UUID     │             │
│ product_id         │ UUID     │             │
│ user_id            │ UUID     │             │
│ delivery_note_id   │ UUID     │             │
│ order_id           │ UUID     │ ✅ NUEVO    │ ← CLAVE
│ quantity           │ INTEGER  │             │
│ type               │ VARCHAR  │             │
│ reason             │ VARCHAR  │             │
└────────────────────┴──────────┴─────────────┘
         │
         └──────────> FK → orders.id
```

---

## 📱 Interfaz de Filament

### Recurso: Movimientos de Stock

```
┌─────────────────────────────────────────────────────────────────┐
│ 🔍 Buscar: [________]  📊 Filtrar: [Tipo ▼] [Producto ▼]       │
├─────────────────────────────────────────────────────────────────┤
│ Fecha           │ Producto    │ Tipo   │ Cant │ Ticket        │👁️│
├─────────────────┼─────────────┼────────┼──────┼───────────────┼──┤
│ 21/01 14:30     │ Coca-Cola   │ Venta  │ -2   │ #20260121-42  │👁️│
│ 21/01 13:15     │ Hamburguesa │ Venta  │ -5   │ #20260121-41  │👁️│
│ 21/01 10:00     │ Patatas     │ Compra │ +50  │ -             │  │
│ 20/01 18:45     │ Cerveza     │ Venta  │ -12  │ #20260120-89  │👁️│
└─────────────────┴─────────────┴────────┴──────┴───────────────┴──┘
                                                                  │
                                                                  │
                  Al hacer clic en 👁️ →                          │
                                                                  │
        ┌─────────────────────────────────────────────────┐     │
        │           📄 Ticket #20260121-0042              │◄────┘
        ├─────────────────────────────────────────────────┤
        │                                                 │
        │  RESTAURANTE SERVI2                            │
        │  ════════════════════                          │
        │                                                 │
        │  Ticket: #20260121-0042                        │
        │  Fecha: 21/01/2026 14:30                       │
        │  Camarero: Juan Pérez                          │
        │                                                 │
        │  ────────────────────────────                  │
        │  2x Coca-Cola        5.00€                     │
        │  ────────────────────────────                  │
        │                                                 │
        │  SUBTOTAL:           4.55€                     │
        │  IVA (10%):          0.45€                     │
        │  ────────────────────────────                  │
        │  TOTAL:              5.00€                     │
        │                                                 │
        │  Método: Efectivo                              │
        │                                                 │
        │         [ Cerrar ]                             │
        └─────────────────────────────────────────────────┘
```

---

## 🧪 Testing Incluido

### Archivo: `tests/Feature/OrderTerminalRefactoredTest.php`

Pruebas implementadas:
- ✅ `al_abrir_modal_de_pago_se_genera_ticket_inmediatamente`
- ✅ `al_confirmar_pago_se_actualiza_orden_y_descuenta_stock`
- ✅ `al_cancelar_pago_se_elimina_la_orden`
- ✅ `no_se_puede_vender_mas_stock_del_disponible`
- ✅ `movimiento_de_stock_puede_acceder_a_su_orden`
- ✅ `orden_puede_acceder_a_sus_movimientos_de_stock`
- ✅ `productos_sin_track_stock_no_generan_movimientos`

Ejecutar tests:
```bash
docker compose -f enviroment/docker-compose.yml exec web php artisan test --filter=OrderTerminalRefactoredTest
```

---

## 📦 Archivos del Proyecto

```
Servi2/
├── app/
│   ├── Filament/
│   │   └── Resources/
│   │       └── StockMovements/               ← NUEVO
│   │           ├── StockMovementResource.php
│   │           ├── Tables/
│   │           │   └── StockMovementsTable.php
│   │           └── Pages/
│   │               ├── ListStockMovements.php
│   │               └── ViewStockMovement.php
│   ├── Livewire/
│   │   └── Pos/
│   │       └── OrderTerminal.php             ← MODIFICADO
│   └── Models/
│       ├── Order.php                         ← MODIFICADO
│       └── StockMovement.php                 ← MODIFICADO
├── database/
│   └── migrations/
│       └── 2026_01_21_150000_refactor_...php ← NUEVO
├── tests/
│   └── Feature/
│       └── OrderTerminalRefactoredTest.php   ← NUEVO
├── database.dbml                             ← MODIFICADO
├── REFACTORIZACION_TPV.md                    ← NUEVO
├── RESUMEN_REFACTORIZACION.md                ← NUEVO
└── artisan.sh                                ← NUEVO
```

---

## ⚡ Comandos Rápidos

```bash
# 1. Aplicar cambios en DB
./artisan.sh migrate

# 2. Limpiar cachés
./artisan.sh optimize:clear

# 3. Ejecutar tests
./artisan.sh test --filter=OrderTerminalRefactoredTest

# 4. Ver estado de migraciones
./artisan.sh migrate:status

# 5. Ver rutas de Filament
./artisan.sh route:list --name=filament
```

---

## 🎓 Conceptos Clave

### Atomicidad
Todas las operaciones críticas usan `DB::transaction()`:
- Crear orden + items → ROLLBACK si falla
- Confirmar pago + stock → ROLLBACK si falla

### Trazabilidad
```
Order ←─────┐
│           │
├─ OrderItems
│           │
└─ StockMovements ─┘
      ↑
      └─ order_id (NUEVA RELACIÓN)
```

### Integridad Referencial
- `order_id` en `stock_movements` → FK con `nullOnDelete`
- Si se elimina una orden, los movimientos quedan con `order_id = NULL`
- Nunca se pierde información histórica de stock

---

## 📞 Próximos Pasos

1. **Ejecutar migración** en el servidor
2. **Probar flujo completo** en TPV
3. **Verificar Filament** → Movimientos de Stock
4. **Revisar logs** para posibles errores
5. **Ejecutar tests** para validación automatizada

---

**¿Listo para desplegar?** ✅  
**Documentación completa:** `REFACTORIZACION_TPV.md`  
**Tests incluidos:** `tests/Feature/OrderTerminalRefactoredTest.php`

---

_Implementado con ❤️ por un Arquitecto de Software Senior especializado en Laravel 11, Livewire 3 y PostgreSQL_
