# Resumen de Cambios - Sistema de Gestión de Compras

## Fecha: 21 de enero de 2026

## Cambios Implementados

### 1. Migraciones Creadas ✅

**Archivo:** `2026_01_21_140000_create_purchase_management_tables.php`

Se crearon 5 nuevas tablas:

#### `budgets` (Presupuestos)
- Almacena presupuestos solicitados a proveedores
- Relación con `suppliers`
- Campos: supplier_id, date, tax_base, tax_rate_quantity, notes
- Soft deletes habilitado

#### `budget_details` (Líneas de Presupuesto)
- Detalle de productos en cada presupuesto
- Relación con `budgets` y `products`
- Campos: budget_id, product_id, quantity, tax_base, tax_rate_quantity, total, notes
- Soft deletes habilitado
- Auto-calcula totales

#### `invoices` (Facturas)
- Facturas recibidas de proveedores
- Relación con `suppliers`
- Campos: supplier_id, date, tax_base, tax_rate_quantity
- Soft deletes habilitado

#### `delivery_notes` (Albaranes de Entrega)
- Registro de mercancía recibida
- Relación opcional con `budgets` e `invoices`
- Campos: budget_id, invoice_id, date, tax_base, tax_rate_quantity, stored
- Campo `stored` indica si ya se sumó al inventario
- Soft deletes habilitado

#### `delivery_note_details` (Líneas de Albarán)
- Detalle de productos recibidos
- Relación con `delivery_notes` y `products`
- Campos: delivery_note_id, product_id, quantity, tax_base, tax_rate_quantity, total, notes
- Soft deletes habilitado

#### Modificación a `stock_movements`
- Se agregó columna `delivery_note_id` (nullable)
- Permite vincular movimientos de stock con albaranes de compra
- Foreign key con `delivery_notes`

### 2. Modelos Eloquent Creados ✅

#### `Budget` (`app/Models/Budget.php`)
- Método `recalculateTotals()` para recalcular automáticamente
- Accessor `total` para obtener base + impuestos
- Relaciones: supplier, details, deliveryNotes

#### `BudgetDetail` (`app/Models/BudgetDetail.php`)
- Auto-calcula el total en el evento `saving`
- Recalcula totales del presupuesto padre en eventos: created, updated, deleted

#### `Invoice` (`app/Models/Invoice.php`)
- Accessor `total` para obtener base + impuestos
- Relaciones: supplier, deliveryNotes

#### `DeliveryNote` (`app/Models/DeliveryNote.php`)
- Método `recalculateTotals()` para recalcular automáticamente
- Método `storeInInventory(User $user)` para almacenar en stock
- Scopes: `stored()`, `pending()`
- Relaciones: budget, invoice, details, stockMovements

#### `DeliveryNoteDetail` (`app/Models/DeliveryNoteDetail.php`)
- Auto-calcula el total en el evento `saving`
- Recalcula totales del albarán padre en eventos: created, updated, deleted

### 3. Modelos Actualizados ✅

#### `Supplier` (`app/Models/Supplier.php`)
- Agregadas relaciones: `budgets()`, `invoices()`

#### `StockMovement` (`app/Models/StockMovement.php`)
- Agregado `delivery_note_id` al fillable
- Agregada relación: `deliveryNote()`

### 4. Factories Creadas ✅

- `BudgetFactory.php` - Para crear presupuestos de prueba
- `InvoiceFactory.php` - Para crear facturas de prueba
- `DeliveryNoteFactory.php` - Para crear albaranes con estados: fromBudget(), withInvoice(), stored()

### 5. Seeders Creados ✅

#### `PurchaseManagementSeeder.php`
- Crea presupuestos con líneas de detalle
- Crea albaranes vinculados a presupuestos
- Crea facturas de ejemplo
- Muestra estadísticas al finalizar

### 6. Documentación Creada ✅

#### `PURCHASE_SYSTEM.md`
- Descripción completa del sistema
- Flujos de trabajo típicos
- Ejemplos de uso con código
- Consultas útiles
- Reglas de negocio

#### `database.dbml`
- Actualizado con todas las nuevas tablas
- Relaciones documentadas
- Índices especificados

## Funcionalidades Principales

### ✅ Gestión de Presupuestos
- Crear presupuestos con múltiples líneas
- Auto-cálculo de totales y subtotales
- Vinculación con proveedores y productos

### ✅ Gestión de Albaranes
- Crear albaranes desde presupuestos o directamente
- Marcar albaranes como "almacenados"
- Método `storeInInventory()` que:
  - Crea movimientos de stock automáticamente
  - Incrementa el stock de productos
  - Marca el albarán como procesado
  - Registra el usuario que almacenó

### ✅ Gestión de Facturas
- Registro de facturas de proveedores
- Vinculación con albaranes

### ✅ Trazabilidad Completa
- Los `stock_movements` ahora tienen referencia al albarán
- Soft deletes en todas las tablas para auditoría histórica
- Timestamps en todas las operaciones

## Datos de Prueba Generados

```
✅ Sistema de gestión de compras poblado exitosamente!
   - Presupuestos: 3
   - Líneas de presupuesto: 18
   - Albaranes: 2
   - Líneas de albarán: 13
   - Facturas: 2
```

## Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder
php artisan db:seed --class=PurchaseManagementSeeder

# Verificar modelos
php artisan model:show Budget
php artisan model:show DeliveryNote
```

## Próximos Pasos Recomendados

1. **Recursos de Filament**
   - Crear `BudgetResource` para gestionar presupuestos
   - Crear `DeliveryNoteResource` para gestionar albaranes
   - Crear `InvoiceResource` para gestionar facturas

2. **Validaciones Adicionales**
   - Validar que un albarán no se almacene dos veces
   - Validar cantidades positivas
   - Validar que los productos existan y estén activos

3. **Reportes**
   - Reporte de compras por período
   - Reporte de productos más comprados
   - Comparativa de presupuestos entre proveedores

4. **Notificaciones**
   - Avisar cuando un albarán esté pendiente de almacenar
   - Alertas de discrepancias entre presupuesto y recepción

5. **API/Endpoints**
   - Endpoints para aplicaciones móviles
   - Importación de albaranes desde CSV/Excel

## Estado Final

✅ **Base de datos actualizada**
✅ **Modelos creados y probados**
✅ **Relaciones funcionando correctamente**
✅ **Datos de prueba generados**
✅ **Documentación completa**

El sistema está listo para ser integrado con la interfaz de usuario (Filament).
