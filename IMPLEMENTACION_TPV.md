# Sistema TPV - Implementación Completa

## 📋 Resumen de Implementación

Se ha implementado un sistema completo de TPV (Terminal Punto de Venta) para HORECA con las siguientes características:

### ✅ TAREA 1: Listener de Notificación de Stock Bajo

**Archivo:** `app/Listeners/CheckLowStock.php`

#### Funcionalidad:
- Escucha el evento `OrderPlaced`
- Recorre todos los ítems de la orden
- Verifica si el producto tiene `track_stock` activado
- Compara `stock_quantity` con `low_stock_threshold`
- Envía notificación de Filament a todos los usuarios si el stock es bajo

#### Características de la Notificación:
- **Tipo:** Warning (⚠️)
- **Título:** "⚠️ Stock Bajo: [Nombre del Producto]"
- **Cuerpo:** "Quedan X unidades. Stock de seguridad: Y"
- **Acción:** Botón "Ver Producto" que redirige a `/admin/products/{uuid}/edit`
- **Destinatarios:** Todos los usuarios del sistema

#### Uso:
```php
use App\Events\OrderPlaced;
use App\Models\Order;

// Al crear una orden, disparar el evento
$order = Order::create([...]);
event(new OrderPlaced($order));

// El listener CheckLowStock se ejecutará automáticamente
```

---

### ✅ TAREA 2: Seeders Realistas para Restaurante

#### 1. **UserSeeder** - `database/seeders/UserSeeder.php`
Crea:
- 1 Administrador (admin@admin.com / password)
- 3 Camareros:
  - Carlos García (carlos@restaurant.com)
  - María López (maria@restaurant.com)
  - Juan Pérez (juan@restaurant.com)

#### 2. **RestaurantTableSeeder** - `database/seeders/RestaurantTableSeeder.php`
Crea 10 mesas:
- Mesas interiores: Mesa 1-5 (capacidad variable: 2-6 comensales)
- Terraza: Terraza 1-5 (capacidad variable: 2-6 comensales)

#### 3. **CategorySeeder** - `database/seeders/CategorySeeder.php`
Categorías de hostelería con colores hex:
- 🔵 Bebidas (#3B82F6 - Azul)
- 🟤 Cafés (#92400E - Marrón)
- 🟢 Entrantes (#10B981 - Verde)
- 🔴 Platos Principales (#EF4444 - Rojo)
- 🟠 Postres (#F59E0B - Naranja)

#### 4. **SupplierSeeder** - `database/seeders/SupplierSeeder.php`
3 proveedores ficticios:
- Distribuciones Bebidas SL (Pedro Martínez)
- Carnicería Selecta (Ana Ruiz)
- Productos Gourmet SA (Luis González)

#### 5. **ProductSeeder** - `database/seeders/ProductSeeder.php`
20 productos variados:

##### Bebidas (4 productos)
- Coca-Cola 33cl - 2.50€ - **Stock: 50** ✅
- Agua Mineral 50cl - 1.50€ - **Stock: 100** ✅
- Cerveza Estrella Galicia - 3.00€ - **Stock: 8** ⚠️ BAJO
- Vino Tinto Crianza - 15.00€ - **Stock: 25** ✅

##### Cafés (3 productos - sin control de stock)
- Café Solo - 1.20€
- Café con Leche - 1.50€
- Cappuccino - 1.80€

##### Entrantes (3 productos)
- Ensalada Mixta - 6.50€ - Sin stock
- Croquetas Caseras - 5.50€ - **Stock: 30** ✅
- Patatas Bravas - 4.50€ - **Stock: 3** ⚠️ MUY BAJO

##### Platos Principales (4 productos)
- Entrecot de Ternera - 18.50€ - **Stock: 15** ✅
- Pollo al Ajillo - 12.00€ - **Stock: 20** ✅
- Paella Valenciana - 14.00€ - **Stock: 5** ⚠️ LÍMITE
- Merluza a la Plancha - 16.50€ - **Stock: 12** ✅

##### Postres (4 productos)
- Tarta de Queso - 5.00€ - **Stock: 10** ✅
- Flan Casero - 3.50€ - **Stock: 20** ✅
- Helado (3 bolas) - 4.50€ - **Stock: 2** ⚠️ MUY BAJO
- Tiramisú - 5.50€ - **Stock: 15** ✅

#### 6. **DatabaseSeeder** - `database/seeders/DatabaseSeeder.php`
Ejecuta todos los seeders en el orden correcto:
1. UserSeeder
2. RestaurantTableSeeder
3. CategorySeeder
4. SupplierSeeder
5. ProductSeeder

---

## 🗄️ Estructura de Base de Datos

### Tablas Creadas:

1. **users** - Usuarios del sistema (admin y camareros)
2. **restaurant_tables** - Mesas del restaurante
3. **categories** - Categorías de productos
4. **suppliers** - Proveedores
5. **products** - Productos del menú
6. **orders** - Pedidos/Comandas
7. **order_items** - Ítems de cada pedido
8. **stock_movements** - Movimientos de inventario

---

## 💰 Importante: Precios en Céntimos

**TODOS los precios se guardan como ENTEROS (céntimos):**
- `cost_price: 250` = 2.50€
- `sale_price: 1500` = 15.00€
- `tax_rate: 1000` = 10.00%
- `tax_rate: 2100` = 21.00%

### Conversión:
```php
// Guardar en DB
$precioEnCentimos = 2.50 * 100; // 250

// Mostrar al usuario
$precioEnEuros = 250 / 100; // 2.50
```

---

## 🚀 Comandos de Instalación

```bash
# 1. Ejecutar migraciones frescas
docker-compose exec web php artisan migrate:fresh

# 2. Ejecutar seeders
docker-compose exec web php artisan db:seed

# 3. (Opcional) Todo en un comando
docker-compose exec web php artisan migrate:fresh --seed
```

---

## 🧪 Probar la Funcionalidad de Stock Bajo

```php
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Events\OrderPlaced;

// 1. Obtener un producto con stock bajo
$cerveza = Product::where('sku', 'BEB-003')->first(); // Stock: 8, Límite: 15

// 2. Crear una orden
$order = Order::create([
    'user_id' => User::first()->id,
    'status' => 'pending',
    'total' => 300,
]);

// 3. Agregar ítem del producto con stock bajo
OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $cerveza->id,
    'quantity' => 1,
    'unit_price' => $cerveza->sale_price,
    'tax_rate' => $cerveza->tax_rate,
    'subtotal' => $cerveza->sale_price,
]);

// 4. Disparar el evento
event(new OrderPlaced($order));

// 5. ✅ Todos los usuarios recibirán una notificación de stock bajo
```

---

## 📊 Productos con Stock Bajo (Para Testing)

Los siguientes productos tienen stock bajo intencionalmente para probar las notificaciones:

| Producto | Stock Actual | Stock Mínimo | Estado |
|----------|-------------|--------------|--------|
| Cerveza Estrella Galicia | 8 | 15 | ⚠️ BAJO |
| Patatas Bravas | 3 | 5 | ⚠️ BAJO |
| Paella Valenciana | 5 | 5 | ⚠️ LÍMITE |
| Helado (3 bolas) | 2 | 5 | ⚠️ BAJO |

---

## 🔔 Configuración de Notificaciones Filament

El sistema utiliza **Filament Database Notifications**. Para visualizar las notificaciones en el panel admin:

1. Las notificaciones se guardan automáticamente en la tabla `notifications`
2. Se muestran en el ícono de campana del panel Filament
3. El botón "Ver Producto" redirige directamente a la edición del producto

---

## 📝 Archivos Creados/Modificados

### Modelos:
- ✅ `app/Models/Order.php`
- ✅ `app/Models/OrderItem.php`
- ✅ `app/Models/RestaurantTable.php`

### Eventos y Listeners:
- ✅ `app/Events/OrderPlaced.php`
- ✅ `app/Listeners/CheckLowStock.php`
- ✅ `app/Providers/EventServiceProvider.php`

### Migraciones:
- ✅ `database/migrations/2026_01_20_120000_create_restaurant_tables.php`
- ✅ `database/migrations/2026_01_20_120001_create_orders_tables.php`

### Seeders:
- ✅ `database/seeders/UserSeeder.php`
- ✅ `database/seeders/RestaurantTableSeeder.php`
- ✅ `database/seeders/CategorySeeder.php`
- ✅ `database/seeders/SupplierSeeder.php`
- ✅ `database/seeders/ProductSeeder.php`
- ✅ `database/seeders/DatabaseSeeder.php`

### Configuración:
- ✅ `bootstrap/providers.php` (registrado EventServiceProvider)

---

## ✨ Características Destacadas

1. **UUIDs v7** en todos los modelos (mejor performance que UUIDs v4)
2. **Precios en céntimos** para evitar errores de redondeo
3. **Control de stock opcional** (`track_stock`) para servicios como cafés
4. **Notificaciones en tiempo real** con Filament
5. **Datos realistas** de un restaurante real
6. **Productos con stock bajo** para testing inmediato
7. **Trazabilidad completa** con relaciones bien definidas

---

## 🎯 Próximos Pasos Sugeridos

1. Crear un Resource de Filament para Orders
2. Implementar reducción automática de stock al crear órdenes
3. Agregar dashboard con estadísticas de ventas
4. Implementar impresión de tickets/comandas
5. Agregar notificaciones por email para stock crítico
6. Crear reportes de ventas por categoría/producto

---

## 📞 Soporte

Para cualquier duda sobre la implementación, revisar:
- **Listener:** `app/Listeners/CheckLowStock.php`
- **Evento:** `app/Events/OrderPlaced.php`
- **Seeders:** `database/seeders/`
- **Migraciones:** `database/migrations/`
