# Servi2 - Sistema TPV e Inventario

Sistema de Punto de Venta (TPV) y gestión de inventario para el sector HORECA desarrollado con Laravel 11 y Filament v5.

## 📋 Descripción

Servi2 es una aplicación web completa para la gestión de inventario y punto de venta, diseñada específicamente para restaurantes, bares y cafeterías. Facilita el control de productos, categorías, proveedores, movimientos de stock y gestión de comandas con trazabilidad completa y notificaciones automáticas.

## 🚀 Características Principales

### Sistema TPV (Punto de Venta)
- **Gestión de Mesas**: Control de mesas de restaurante (interior y terraza) con capacidad
- **Órdenes/Comandas**: Sistema completo de pedidos con ítems y estados
- **Notificaciones Automáticas**: Alertas de stock bajo en tiempo real con Filament
- **Historial de Pedidos**: Trazabilidad completa de todas las órdenes por usuario

### Gestión de Inventario
- **Productos**: CRUD completo con UUID v7, soft deletes, códigos de barras y SKU únicos
- **Categorías**: Organización de productos con colores personalizados (Bebidas, Cafés, Entrantes, Platos, Postres)
- **Proveedores**: Gestión de información de proveedores con productos asociados
- **Movimientos de Stock**: Trazabilidad completa de todos los movimientos (compras, ventas, ajustes, mermas)

### Control de Stock Inteligente
- Bloqueo del campo stock en formularios (solo modificable mediante ajustes)
- Acción de ajuste de stock con modal integrado en Filament
- **Listener CheckLowStock**: Notificaciones automáticas cuando el stock está bajo el umbral
- Historial de movimientos con relation manager
- Scopes para productos con stock bajo (`lowStock()`)
- Cálculo automático de precios con impuestos incluidos
- Control opcional de stock (ideal para servicios como cafés que no requieren tracking)

### Panel Administrativo Filament v5
- Interfaz moderna y responsive construida con **Filament v5**
- **Notificaciones en tiempo real** con Filament Database Notifications
- Formularios dinámicos con validaciones
- Tablas con filtros, búsqueda y ordenamiento
- Relation managers para visualizar relaciones entre modelos
- Sistema de acciones personalizadas

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 11 (PHP 8.3)
- **Admin Panel**: Filament v5
- **Base de Datos**: PostgreSQL
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS
- **Containerización**: Docker + Docker Compose
- **Testing**: PHPUnit (141 tests, 265 aserciones)
- **Arquitectura**: Event-Driven (Events & Listeners)

## 📦 Requisitos Previos

- Docker Engine 20.10 o superior
- Docker Compose v2.0 o superior
- Git

## 🔧 Instalación y Configuración

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/Servi2.git
cd Servi2
```

### 2. Levantar los contenedores
```bash
docker-compose up -d
```

### 3. Instalar dependencias
```bash
docker-compose exec web composer install
docker-compose exec web npm install
docker-compose exec web npm run build
```

### 4. Configurar el entorno
```bash
docker-compose exec web cp .env.example .env
docker-compose exec web php artisan key:generate
```

### 5. Ejecutar migraciones y seeders
```bash
docker-compose exec web php artisan migrate:fresh --seed
```

Este comando creará automáticamente:
- ✅ 1 usuario administrador (admin@admin.com / password)
- ✅ 3 camareros (Carlos, María, Juan)
- ✅ 10 mesas de restaurante (5 interiores + 5 terraza)
- ✅ 5 categorías de productos con colores
- ✅ 3 proveedores (Distribuciones Bebidas, Carnicería Selecta, Productos Gourmet)
- ✅ 20 productos variados (algunos con stock bajo para testing)

### 6. Acceder al panel de administración
- **URL**: http://localhost/admin
- **Email**: admin@admin.com
- **Password**: password

## 📊 Datos de Prueba

El sistema incluye datos realistas de un restaurante:

### Categorías (con colores hex)
- 🔵 **Bebidas** (#3B82F6) - Coca-Cola, Agua, Cerveza, Vino
- 🟤 **Cafés** (#92400E) - Café Solo, Café con Leche, Cappuccino
- 🟢 **Entrantes** (#10B981) - Ensalada, Croquetas, Patatas Bravas
- 🔴 **Platos Principales** (#EF4444) - Entrecot, Pollo, Paella, Merluza
- 🟠 **Postres** (#F59E0B) - Tarta de Queso, Flan, Helado, Tiramisú

### Productos con Stock Bajo (para testing de notificaciones)
- ⚠️ **Cerveza Estrella Galicia**: Stock 8 / Mínimo 15
- ⚠️ **Patatas Bravas**: Stock 3 / Mínimo 5
- ⚠️ **Paella Valenciana**: Stock 5 / Mínimo 5
- ⚠️ **Helado (3 bolas)**: Stock 2 / Mínimo 5

## 🔔 Sistema de Notificaciones de Stock Bajo

El sistema incluye un **Listener automático** que detecta cuando el stock está bajo.

### ¿Cómo funciona?
1. Al crear una orden (pedido), se dispara el evento `OrderPlaced`
2. El listener `CheckLowStock` verifica cada producto de la orden
3. Si `stock_quantity` ≤ `low_stock_threshold` y `track_stock` está activado
4. Se envía una notificación de Filament a todos los usuarios con:
   - **Tipo**: Warning ⚠️
   - **Título**: "Stock Bajo: [Nombre Producto]"
   - **Cuerpo**: "Quedan X unidades. Stock de seguridad: Y"
   - **Acción**: Botón "Ver Producto" (redirige a `/admin/products/{uuid}/edit`)

### Probar las notificaciones
```bash
# Entrar al tinker de Laravel
docker-compose exec web php artisan tinker

# Ejecutar el script de prueba incluido
include 'development/test_low_stock_listener.php';
```

📄 **Documentación completa**: [IMPLEMENTACION_TPV.md](IMPLEMENTACION_TPV.md)

## 🧪 Testing

El proyecto cuenta con **141 tests** (265 aserciones) que cubren todos los modelos.

### Ejecutar todos los tests
```bash
docker-compose exec web php artisan test
```

### Tests específicos por modelo
```bash
# Category
docker-compose exec web php artisan test tests/Feature/CategoryTest.php
docker-compose exec web php artisan test tests/Unit/CategoryUnitTest.php

# Product
docker-compose exec web php artisan test tests/Feature/ProductTest.php
docker-compose exec web php artisan test tests/Unit/ProductUnitTest.php

# StockMovement
docker-compose exec web php artisan test tests/Feature/StockMovementTest.php
docker-compose exec web php artisan test tests/Unit/StockMovementUnitTest.php

# Supplier
docker-compose exec web php artisan test tests/Feature/SupplierTest.php
docker-compose exec web php artisan test tests/Unit/SupplierUnitTest.php
```

### Cobertura de Tests

| Modelo | Feature Tests | Unit Tests | Total |
|--------|--------------|------------|-------|
| Category | 13 | 11 | 24 |
| Product | 22 | 21 | 43 |
| StockMovement | 20 | 19 | 39 |
| Supplier | 17 | 17 | 34 |
| **TOTAL** | **72** | **68** | **141** |

## 📁 Estructura del Proyecto

```
Servi2/
├── development/                    # Código fuente Laravel
│   ├── app/
│   │   ├── Events/
│   │   │   └── OrderPlaced.php
│   │   ├── Listeners/
│   │   │   └── CheckLowStock.php
│   │   ├── Filament/
│   │   │   └── Resources/
│   │   │       └── Products/
│   │   ├── Models/
│   │   │   ├── Category.php
│   │   │   ├── Product.php
│   │   │   ├── StockMovement.php
│   │   │   ├── Supplier.php
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   └── RestaurantTable.php
│   │   └── Providers/
│   ├── database/
│   │   ├── factories/              # 6 factories con estados
│   │   ├── migrations/             # 6 migraciones
│   │   └── seeders/                # 6 seeders realistas
│   ├── tests/
│   │   ├── Feature/                # 72 tests de integración
│   │   └── Unit/                   # 68 tests unitarios
│   └── test_low_stock_listener.php # Script de prueba
├── web/                            # Configuración Docker
│   ├── Dockerfile
│   └── entrypoint.sh
├── docker-compose.yml
├── IMPLEMENTACION_TPV.md           # Documentación técnica completa
└── README.md
```

## 🗄️ Modelos del Sistema

### RestaurantTable
- Gestión de mesas del restaurante
- Control de disponibilidad (`is_available`)
- Capacidad de comensales
- Relación: `hasMany(Order)`

### Order
- Pedidos/Comandas del restaurante
- Estados: `pending`, `completed`, `cancelled`
- Relación con mesa y usuario (camarero)
- **Precios en céntimos** (enteros para evitar redondeo)
- Relación: `hasMany(OrderItem)`

### OrderItem
- Ítems individuales de cada pedido
- Precio histórico (momento de la venta)
- Cantidad, subtotal, IVA
- Relaciones: `belongsTo(Order)`, `belongsTo(Product)`

### Product
- UUID v7 como clave primaria
- Soft deletes
- Barcode y SKU únicos
- Scopes: `active()`, `lowStock()`
- Accessors: formateo de precios, cálculo de IVA
- `track_stock`: Control opcional de inventario
- Relaciones: `belongsTo(Category)`, `belongsTo(Supplier)`, `hasMany(StockMovement)`

### Category
- Soft deletes
- Slug único para URLs
- Color hex personalizado
- Scope: `active()`
- Relación: `hasMany(Product)`

### Supplier
- Información de proveedores
- Campos opcionales de contacto
- Relación: `hasMany(Product)`

### StockMovement
- Trazabilidad de movimientos de inventario
- Tipos: `purchase`, `sale`, `adjustment`, `waste`
- Scopes: `byType()`, `forProduct()`
- Relaciones: `belongsTo(Product)`, `belongsTo(User)`

## 🔑 Factories y Estados

Cada modelo incluye factories con estados personalizados:

- **CategoryFactory**: `active()`, `inactive()`
- **ProductFactory**: `active()`, `inactive()`, `outOfStock()`, `lowStock()`
- **StockMovementFactory**: `purchase()`, `sale()`, `adjustment()`, `waste()`
- **SupplierFactory**: Datos realistas de proveedores

## 💰 Importante: Precios en Céntimos

**TODOS los precios se almacenan como enteros (céntimos)** para evitar errores de redondeo:

```php
// Guardar en DB
$cost_price = 250;     // 2.50€
$sale_price = 1500;    // 15.00€
$tax_rate = 1000;      // 10.00%

// Mostrar al usuario
$precioEuros = $cost_price / 100;  // 2.50
```

## 🎯 Uso del Sistema

### Crear una Orden con Notificación de Stock Bajo
```php
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Events\OrderPlaced;

// 1. Obtener producto con stock bajo
$cerveza = Product::where('sku', 'BEB-003')->first();

// 2. Crear orden
$order = Order::create([
    'user_id' => auth()->id(),
    'status' => 'pending',
    'total' => 0,
]);

// 3. Agregar ítem
OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $cerveza->id,
    'quantity' => 2,
    'unit_price' => $cerveza->sale_price,
    'tax_rate' => $cerveza->tax_rate,
    'subtotal' => $cerveza->sale_price * 2,
]);

// 4. Disparar evento (automático en producción)
event(new OrderPlaced($order));

// 5. ✅ Todos los usuarios recibirán notificación si stock_quantity <= low_stock_threshold
```

## 🐳 Comandos Docker Útiles

### Gestión de Contenedores
```bash
# Ver logs en tiempo real
docker-compose logs -f

# Reiniciar servicios
docker-compose restart

# Detener servicios
docker-compose down

# Reconstruir contenedores
docker-compose build --no-cache
docker-compose up -d
```

### Laravel Artisan
```bash
# Limpiar caché
docker-compose exec web php artisan cache:clear
docker-compose exec web php artisan config:clear
docker-compose exec web php artisan view:clear

# Ejecutar migraciones
docker-compose exec web php artisan migrate
docker-compose exec web php artisan migrate:rollback

# Acceso a tinker
docker-compose exec web php artisan tinker
```

## 📚 Documentación Adicional

- 📄 [Implementación Técnica Completa](IMPLEMENTACION_TPV.md) - Detalles del Listener, Seeders y arquitectura
- 🧪 [Script de Prueba](development/test_low_stock_listener.php) - Ejemplos de uso del sistema de notificaciones

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto utiliza Laravel, que es software de código abierto licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## 👤 Contacto

**Cristobal Jurado Oller** - [@Cjuol](https://github.com/Cjuol)

**Plantilla Docker**: [docker-env](https://github.com/cjuol/docker-env)

---

# 📦 Guía del Entorno de Desarrollo Docker

Este proyecto utiliza un entorno de desarrollo completamente containerizado con Docker. A continuación se detalla cómo funciona y cómo utilizarlo.

## Componentes del Entorno

### Servicios Docker

El entorno incluye dos servicios principales definidos en [docker-compose.yml](docker-compose.yml):

1. **Web (Apache + PHP 8.3)**
   - Puerto: 80
   - Incluye: Composer, PHPUnit, extensiones PHP comunes
   - Directorio de trabajo: `/var/www/html/demo`

2. **Base de Datos (MariaDB)**
   - Puerto: 3306
   - Versión: MariaDB (última estable)
   - Persistencia: Volumen Docker

### Credenciales de Base de Datos

Las credenciales por defecto están en [docker-compose.yml](docker-compose.yml):

- **Host:** `db`
- **Puerto:** `3306`
- **Base de datos:** `demo_db`
- **Usuario:** `demo_user`
- **Contraseña:** `demo_password`
- **Usuario root:** `root`
- **Contraseña root:** `example`

⚠️ **Importante:** Cambia estas credenciales antes de usar en producción.

## Comandos Docker Útiles

### Gestión de Contenedores

```bash
# Iniciar los contenedores
docker-compose up -d

# Detener los contenedores
docker-compose down

# Reiniciar los contenedores
docker-compose restart

# Ver estado de los contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs solo del servicio web
docker logs -f docker-env-web-1
```

### Acceso a los Contenedores

```bash
# Acceder al contenedor web (bash interactivo)
docker exec -it docker-env-web-1 bash

# Acceder al contenedor de base de datos
docker exec -it docker-env-db-1 bash
```

### Comandos de Desarrollo

```bash
# Ejecutar Composer
docker exec docker-env-web-1 composer install
docker exec docker-env-web-1 composer update
docker exec docker-env-web-1 composer require [paquete]

# Ejecutar PHPUnit
docker exec docker-env-web-1 phpunit
docker exec docker-env-web-1 phpunit --filter [test-name]

# Ejecutar scripts PHP
docker exec docker-env-web-1 php script.php

# Ejecutar comandos de Symfony (si usas Symfony)
docker exec docker-env-web-1 php bin/console [comando]

# Ejecutar comandos de Laravel (si usas Laravel)
docker exec docker-env-web-1 php artisan [comando]
```

## Configuración del Entorno

### Selección de Framework

El entorno soporta la creación automática de proyectos. Edita [docker-compose.yml](docker-compose.yml) y añade la variable `FRAMEWORK`:

```yaml
services:
  web:
    environment:
      - FRAMEWORK=laravel  # Opciones: symfony, laravel, none
```

**Opciones disponibles:**
- `symfony` - Crea automáticamente un proyecto Symfony 6.4
- `laravel` - Crea automáticamente un proyecto Laravel con Filament y Livewire
- `none` (por defecto) - No crea ningún proyecto automáticamente

**Nota:** La creación solo ocurre si no existe `composer.json` en `development/`

### Directorio de Desarrollo

- **Local:** `./development/`
- **Contenedor:** `/var/www/html/demo`

Todo el código que escribas en `development/` se sincroniza automáticamente con el contenedor.

### Personalización Avanzada

#### Agregar Extensiones PHP

Edita [web/Dockerfile](web/Dockerfile) y añade las extensiones necesarias:

```dockerfile
RUN docker-php-ext-install [extension-name]
```

#### Modificar Inicialización

Edita [web/entrypoint.sh](web/entrypoint.sh) para personalizar lo que ocurre al iniciar el contenedor.

#### Cambiar Puertos

Edita [docker-compose.yml](docker-compose.yml):

```yaml
services:
  web:
    ports:
      - "8080:80"  # Cambiar puerto 80 a 8080
```

## Solución de Problemas

### Los contenedores no inician

```bash
# Ver logs detallados
docker-compose logs

# Reconstruir los contenedores
docker-compose build --no-cache
docker-compose up -d
```

### Error de permisos en archivos

```bash
# Desde dentro del contenedor web
docker exec -it docker-env-web-1 bash
chown -R www-data:www-data /var/www/html/demo
```

### Puerto ya en uso

Si el puerto 80 o 3306 ya está en uso, cambia los puertos en [docker-compose.yml](docker-compose.yml).

### Base de datos no conecta

Verifica que:
- El contenedor de base de datos esté corriendo: `docker-compose ps`
- Las credenciales en tu código coincidan con [docker-compose.yml](docker-compose.yml)
- Uses `db` como host, no `localhost`

## Recursos Adicionales

- [Documentación de Docker](https://docs.docker.com/)
- [Documentación de Docker Compose](https://docs.docker.com/compose/)
- [PHP Docker Official Image](https://hub.docker.com/_/php)

---

**Plantilla creada por:** Cristobal Jurado Oller - [@Cjuol](https://github.com/Cjuol)  
**Repositorio de la plantilla:** [https://github.com/cjuol/docker-env](https://github.com/cjuol/docker-env)