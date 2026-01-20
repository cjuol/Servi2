# Servi2 - Sistema TPV e Inventario

Sistema de Punto de Venta (TPV) y gestión de inventario desarrollado con Laravel 11 y Filament v5.

## 📋 Descripción

Servi2 es una aplicación web completa para la gestión de inventario y punto de venta, diseñada para facilitar el control de productos, categorías, proveedores y movimientos de stock con trazabilidad completa.

## 🚀 Características

### Sistema de Control de Acceso y Usuarios
- **Gestión de Roles (ACL)**: Sistema completo de roles (Admin, Camarero)
- **Permisos Granulares**: Control de acceso con políticas (Policies)
- **Gestión de Empleados**: CRUD completo desde panel administrativo
- **Protección de Rutas**: Solo admins acceden a `/admin`, camareros a `/pos`
- 📄 **[Ver Documentación Completa de ACL](ACL_IMPLEMENTATION.md)**

### Sistema TPV (Punto de Venta)
- **Gestión de Mesas**: Control de mesas de restaurante (interior y terraza)
- **Órdenes/Comandas**: Sistema completo de pedidos con ítems
- **Notificaciones Automáticas**: Alertas de stock bajo en tiempo real
- **Historial de Pedidos**: Trazabilidad completa de todas las órdenes

### Gestión de Inventario
- **Productos**: CRUD completo con UUID v7, soft deletes, códigos de barras y SKU únicos
- **Categorías**: Organización de productos con colores personalizados
- **Proveedores**: Gestión de información de proveedores con productos asociados
- **Movimientos de Stock**: Trazabilidad completa de todos los movimientos (compras, ventas, ajustes, mermas)

### Control de Stock Inteligente
- Bloqueo del campo stock en formularios (solo mediante ajustes)
- Acción de ajuste de stock con modal integrado en Filament
- **Listener CheckLowStock**: Notificaciones automáticas cuando el stock está bajo
- Historial de movimientos con relation manager
- Scopes para productos con stock bajo
- Cálculo automático de precios con impuestos
- Control opcional de stock (para servicios como cafés)

### Panel Administrativo
- Interfaz construida con **Filament v5**
- **Notificaciones en tiempo real** con Filament Database Notifications
- Formularios dinámicos y validaciones
- Tablas con filtros y búsqueda
- Relation managers para visualizar relaciones
- Notificaciones de acciones

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 11
- **Admin Panel**: Filament v5
- **Base de Datos**: PostgreSQL
- **Frontend**: Livewire, Alpine.js, Tailwind CSS
- **Containerización**: Docker + Docker Compose
- **Testing**: PHPUnit

## 📦 Requisitos

- Docker
- Docker Compose
- Git

## 🔧 Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd Servi2
```

2. **Levantar los contenedores**
```bash
docker-compose up -d
```

3. **Instalar dependencias**
```bash
docker-compose exec web composer install
docker-compose exec web npm install
```

4. **Configurar el entorno**
```bash
docker-compose exec web cp .env.example .env
docker-compose exec web php artisan key:generate
```

5. **Ejecutar migraciones y seeders**
```bash
docker-compose exec web php artisan migrate:fresh --seed
```

Este comando creará:
- 1 usuario administrador (admin@admin.com / password)
- 3 camareros
- 10 mesas de restaurante
- 5 categorías de productos
- 3 proveedores
- 20 productos variados (algunos con stock bajo para testing)

6. **Acceder al panel de administración**
- URL: http://localhost/admin
- Email: admin@admin.com
- Password: password

## 🔔 Sistema de Notificaciones de Stock Bajo

El sistema incluye un **Listener automático** que detecta cuando el stock de un producto está bajo y envía notificaciones a todos los usuarios.

### Funcionamiento:
1. Al crear una orden (pedido), se dispara el evento `OrderPlaced`
2. El listener `CheckLowStock` verifica cada producto de la orden
3. Si el `stock_quantity` ≤ `low_stock_threshold` y `track_stock` está activado
4. Se envía una notificación de Filament a todos los usuarios con:
   - Tipo: Warning ⚠️
   - Título: "Stock Bajo: [Nombre Producto]"
   - Cuerpo: "Quedan X unidades. Stock de seguridad: Y"
   - Botón: "Ver Producto" (redirige a edición)

### Probar las notificaciones:
```bash
# Entrar al tinker de Laravel
docker-compose exec web php artisan tinker

# Copiar y pegar el contenido de test_low_stock_listener.php
```

📄 Ver archivo: [test_low_stock_listener.php](test_low_stock_listener.php) para ejemplos completos

### Productos con stock bajo (para testing):
- **Cerveza Estrella Galicia**: Stock 8 / Mínimo 15 ⚠️
- **Patatas Bravas**: Stock 3 / Mínimo 5 ⚠️
- **Paella Valenciana**: Stock 5 / Mínimo 5 ⚠️
- **Helado (3 bolas)**: Stock 2 / Mínimo 5 ⚠️

## 🧪 Testing

El proyecto cuenta con **141 tests** con **265 aserciones** que cubren todos los modelos principales.

### Ejecutar todos los tests
```bash
docker-compose exec web php artisan test
```

### Ejecutar tests específicos

**Tests de Category:**
```bash
docker-compose exec web php artisan test tests/Feature/CategoryTest.php
docker-compose exec web php artisan test tests/Unit/CategoryUnitTest.php
```

**Tests de Product:**
```bash
docker-compose exec web php artisan test tests/Feature/ProductTest.php
docker-compose exec web php artisan test tests/Unit/ProductUnitTest.php
```

**Tests de StockMovement:**
```bash
docker-compose exec web php artisan test tests/Feature/StockMovementTest.php
docker-compose exec web php artisan test tests/Unit/StockMovementUnitTest.php
```

**Tests de Supplier:**
```bash
docker-compose exec web php artisan test tests/Feature/SupplierTest.php
docker-compose exec web php artisan test tests/Unit/SupplierUnitTest.php
```

### Ejecutar tests con cobertura
```bash
docker-compose exec web php artisan test --coverage
```

### Ejecutar un test específico
```bash
docker-compose exec web php artisan test --filter nombre_del_test
```

## 📊 Cobertura de Tests

| Modelo | Feature Tests | Unit Tests | Total |
|--------|--------------|------------|-------|
| Category | 13 | 11 | 24 |
| Product | 22 | 21 | 43 |
| StockMovement | 20 | 19 | 39 |
| Supplier | 17 | 17 | 34 |
| **TOTAL** | **72** | **68** | **141** |

## 📁 Estructura del Proyecto

```
development/
├── app/
│   ├── Events/
│   │   └── OrderPlaced.php
│   ├── Listeners/
│   │   └── CheckLowStock.php
│   ├── Filament/
│   │   └── Resources/
│   │       └── Products/
│   │           ├── ProductResource.php
│   │           ├── Schemas/
│   │           │   └── ProductForm.php
│   │           ├── Tables/
│   │           │   └── ProductsTable.php
│   │           └── RelationManagers/
│   │               └── MovementsRelationManager.php
│   ├── Http/
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── StockMovement.php
│   │   ├── Supplier.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── RestaurantTable.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── EventServiceProvider.php
│       └── Filament/
├── database/
│   ├── factories/
│   │   ├── CategoryFactory.php
│   │   ├── ProductFactory.php
│   │   ├── StockMovementFactory.php
│   │   ├── SupplierFactory.php
│   │   ├── OrderFactory.php
│   │   └── RestaurantTableFactory.php
│   ├── migrations/
│   │   ├── 2026_01_20_103855_create_inventory_tables.php
│   │   ├── 2026_01_20_120000_create_restaurant_tables.php
│   │   └── 2026_01_20_120001_create_orders_tables.php
│   └── seeders/
│       ├── UserSeeder.php
│       ├── RestaurantTableSeeder.php
│       ├── CategorySeeder.php
│       ├── SupplierSeeder.php
│       ├── ProductSeeder.php
│       └── DatabaseSeeder.php
├── tests/
│   ├── Feature/
│   │   ├── CategoryTest.php
│   │   ├── ProductTest.php
│   │   ├── StockMovementTest.php
│   │   └── SupplierTest.php
│   └── Unit/
│       ├── CategoryUnitTest.php
│       ├── ProductUnitTest.php
│       ├── StockMovementUnitTest.php
│       └── SupplierUnitTest.php
├── test_low_stock_listener.php (Script de prueba)
├── IMPLEMENTACION_TPV.md (Documentación completa)
└── routes/
```

## 🗄️ Modelos

### RestaurantTable
- Gestión de mesas del restaurante
- Control de disponibilidad
- Capacidad de comensales

### Order
- Pedidos/Comandas del restaurante
- Estado: pending, completed, cancelled
- Relación con mesa y camarero
- Precios en céntimos (enteros)

### OrderItem
- Ítems individuales de cada pedido
- Precio histórico (momento de la venta)
- Relación con producto

### Category
- Categorización de productos
- Soft deletes
- Slug único
- Color personalizado
- Scope: `active()`

### Product
- Gestión completa de productos
- UUID v7 como clave primaria
- Soft deletes
- Barcode y SKU únicos
- Relaciones: Category, Supplier, StockMovements
- Scopes: `active()`, `lowStock()`
- Accessors: formateo de precios, cálculo de IVA

### Supplier
- Información de proveedores
- Relación con productos
- Campos opcionales de contacto

### StockMovement
- Trazabilidad de movimientos
- Tipos: compra, venta, ajuste, merma
- Relaciones: Product, User
- Scopes: `byType()`, `forProduct()`

## 🔑 Factories

Cada modelo cuenta con factory completo y estados personalizados:

- **CategoryFactory**: `active()`, `inactive()`
- **ProductFactory**: `active()`, `inactive()`, `outOfStock()`, `lowStock()`
- **StockMovementFactory**: `purchase()`, `sale()`, `adjustment()`, `waste()`
- **SupplierFactory**: Datos realistas de proveedores

## 📝 Licencia

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
