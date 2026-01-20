# Servi2 - Sistema TPV e Inventario

Sistema de Punto de Venta (TPV) y gestión de inventario desarrollado con Laravel 11 y Filament v5.

## 📋 Descripción

Servi2 es una aplicación web completa para la gestión de inventario y punto de venta, diseñada para facilitar el control de productos, categorías, proveedores y movimientos de stock con trazabilidad completa.

## 🚀 Características

### Gestión de Inventario
- **Productos**: CRUD completo con UUID v7, soft deletes, códigos de barras y SKU únicos
- **Categorías**: Organización de productos con colores personalizados
- **Proveedores**: Gestión de información de proveedores con productos asociados
- **Movimientos de Stock**: Trazabilidad completa de todos los movimientos (compras, ventas, ajustes, mermas)

### Control de Stock
- Bloqueo del campo stock en formularios (solo mediante ajustes)
- Acción de ajuste de stock con modal integrado en Filament
- Historial de movimientos con relation manager
- Scopes para productos con stock bajo
- Cálculo automático de precios con impuestos

### Panel Administrativo
- Interfaz construida con **Filament v5**
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

5. **Ejecutar migraciones**
```bash
docker-compose exec web php artisan migrate
```

6. **Crear usuario administrador (opcional)**
```bash
docker-compose exec web php artisan make:filament-user
```

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
│   │   └── Supplier.php
│   └── Providers/
├── database/
│   ├── factories/
│   │   ├── CategoryFactory.php
│   │   ├── ProductFactory.php
│   │   ├── StockMovementFactory.php
│   │   └── SupplierFactory.php
│   ├── migrations/
│   └── seeders/
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
└── routes/
```

## 🗄️ Modelos

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
