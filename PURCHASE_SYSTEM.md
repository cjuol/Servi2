# Sistema de Gestión de Compras

## Descripción

Sistema completo para gestionar el ciclo de compras a proveedores, desde presupuestos hasta la recepción de mercancía y su almacenamiento en inventario.

## Flujo de Trabajo

```
Proveedor → Presupuesto → Albarán de Entrega → Factura → Stock
```

## Modelos y Relaciones

### 1. Budget (Presupuesto)
Presupuestos solicitados a proveedores.

**Campos principales:**
- `supplier_id`: Proveedor del presupuesto
- `date`: Fecha del presupuesto
- `tax_base`: Base imponible total (céntimos)
- `tax_rate_quantity`: Total de impuestos (céntimos)
- `notes`: Notas adicionales

**Relaciones:**
- `supplier`: Pertenece a un proveedor
- `details`: Tiene muchas líneas de detalle
- `deliveryNotes`: Puede generar varios albaranes

### 2. BudgetDetail (Línea de Presupuesto)
Líneas individuales de cada presupuesto.

**Campos principales:**
- `budget_id`: Presupuesto al que pertenece
- `product_id`: Producto presupuestado
- `quantity`: Cantidad solicitada
- `tax_base`: Base imponible de la línea
- `tax_rate_quantity`: Impuestos de la línea
- `total`: Total de la línea (auto-calculado)

### 3. Invoice (Factura)
Facturas recibidas de proveedores.

**Campos principales:**
- `supplier_id`: Proveedor que emite la factura
- `date`: Fecha de factura
- `tax_base`: Base imponible total
- `tax_rate_quantity`: Total de impuestos

**Relaciones:**
- `supplier`: Pertenece a un proveedor
- `deliveryNotes`: Tiene varios albaranes asignados

### 4. DeliveryNote (Albarán de Entrega)
Registro de la mercancía recibida.

**Campos principales:**
- `budget_id`: Presupuesto de origen (opcional)
- `invoice_id`: Factura a la que está asignado (opcional)
- `date`: Fecha de recepción
- `stored`: Indica si ya se sumó al stock
- `tax_base`: Base imponible total
- `tax_rate_quantity`: Total de impuestos

**Relaciones:**
- `budget`: Puede venir de un presupuesto
- `invoice`: Puede estar asignado a una factura
- `details`: Tiene líneas de detalle
- `stockMovements`: Genera movimientos de stock

### 5. DeliveryNoteDetail (Línea de Albarán)
Líneas individuales de cada albarán.

**Campos principales:**
- `delivery_note_id`: Albarán al que pertenece
- `product_id`: Producto recibido
- `quantity`: Cantidad recibida
- `tax_base`: Base imponible de la línea
- `tax_rate_quantity`: Impuestos de la línea
- `total`: Total de la línea

## Ejemplos de Uso

### Crear un Presupuesto

```php
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Product;
use App\Models\Supplier;

// 1. Crear el presupuesto
$budget = Budget::create([
    'supplier_id' => $supplier->id,
    'date' => now(),
    'notes' => 'Pedido mensual de bebidas',
]);

// 2. Agregar líneas al presupuesto
$product = Product::find('...');
BudgetDetail::create([
    'budget_id' => $budget->id,
    'product_id' => $product->id,
    'quantity' => 50,
    'tax_base' => 5000, // 50€ sin IVA
    'tax_rate_quantity' => 1050, // 21% IVA
    // total se calcula automáticamente
]);

// Los totales del presupuesto se recalculan automáticamente
```

### Recibir Mercancía (Albarán)

```php
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;

// 1. Crear el albarán de entrega
$deliveryNote = DeliveryNote::create([
    'budget_id' => $budget->id, // Opcional: vincular con presupuesto
    'date' => now(),
    'stored' => false, // Aún no almacenado
]);

// 2. Agregar las líneas de productos recibidos
DeliveryNoteDetail::create([
    'delivery_note_id' => $deliveryNote->id,
    'product_id' => $product->id,
    'quantity' => 48, // Recibimos 48 de las 50 solicitadas
    'tax_base' => 4800,
    'tax_rate_quantity' => 1008,
]);

// 3. Almacenar en inventario
$user = auth()->user();
$deliveryNote->storeInInventory($user);

// Esto automáticamente:
// - Crea movimientos de stock (StockMovement)
// - Incrementa el stock de cada producto
// - Marca el albarán como 'stored'
```

### Consultas Útiles

```php
// Obtener presupuestos de un proveedor
$budgets = $supplier->budgets()
    ->with('details.product')
    ->latest()
    ->get();

// Albaranes pendientes de almacenar
$pending = DeliveryNote::pending()
    ->with('details.product')
    ->get();

// Albaranes ya almacenados
$stored = DeliveryNote::stored()
    ->whereBetween('date', [$startDate, $endDate])
    ->get();

// Total de compras a un proveedor en un período
$total = Invoice::where('supplier_id', $supplier->id)
    ->whereBetween('date', [$startDate, $endDate])
    ->sum('tax_base');

// Movimientos de stock de un albarán
$movements = $deliveryNote->stockMovements()
    ->with('product')
    ->get();

// Productos más comprados
$topProducts = BudgetDetail::selectRaw('product_id, SUM(quantity) as total_quantity')
    ->groupBy('product_id')
    ->orderByDesc('total_quantity')
    ->limit(10)
    ->with('product')
    ->get();
```

## Flujos de Trabajo Típicos

### Flujo 1: Desde Presupuesto hasta Stock

```php
// 1. Solicitar presupuesto
$budget = Budget::create([...]);
$budget->details()->createMany([...]);

// 2. Al recibir mercancía, crear albarán vinculado
$deliveryNote = DeliveryNote::create([
    'budget_id' => $budget->id,
    'date' => now(),
]);

// Copiar líneas del presupuesto (ajustando cantidades si es necesario)
foreach ($budget->details as $detail) {
    DeliveryNoteDetail::create([
        'delivery_note_id' => $deliveryNote->id,
        'product_id' => $detail->product_id,
        'quantity' => $detail->quantity,
        'tax_base' => $detail->tax_base,
        'tax_rate_quantity' => $detail->tax_rate_quantity,
    ]);
}

// 3. Almacenar en inventario
$deliveryNote->storeInInventory(auth()->user());

// 4. Cuando llegue la factura, vincularla
$invoice = Invoice::create([...]);
$deliveryNote->update(['invoice_id' => $invoice->id]);
```

### Flujo 2: Recepción Directa (Sin Presupuesto)

```php
// 1. Crear albarán directamente
$deliveryNote = DeliveryNote::create([
    'date' => now(),
]);

// 2. Agregar productos recibidos
DeliveryNoteDetail::create([
    'delivery_note_id' => $deliveryNote->id,
    'product_id' => $product->id,
    'quantity' => 30,
    'tax_base' => 3000,
    'tax_rate_quantity' => 630,
]);

// 3. Almacenar
$deliveryNote->storeInInventory(auth()->user());
```

## Validaciones y Reglas de Negocio

1. **No duplicar stock**: Un albarán solo se puede almacenar una vez (`stored = true`)
2. **Totales automáticos**: Los totales se recalculan automáticamente al modificar líneas
3. **Soft Deletes**: Todas las tablas principales usan soft deletes para auditoría
4. **Trazabilidad**: Los movimientos de stock mantienen referencia al albarán de origen
5. **Precios en céntimos**: Todos los montos están en céntimos para evitar errores de redondeo

## Seeders

Para poblar el sistema con datos de ejemplo:

```bash
php artisan db:seed --class=PurchaseManagementSeeder
```

## Próximos Pasos

- [ ] Crear recursos de Filament para gestionar presupuestos
- [ ] Crear recursos de Filament para gestionar albaranes
- [ ] Crear recursos de Filament para gestionar facturas
- [ ] Agregar validaciones de negocio adicionales
- [ ] Implementar reportes de compras por período
- [ ] Implementar comparativas entre presupuestos
- [ ] Sistema de aprobación de presupuestos
