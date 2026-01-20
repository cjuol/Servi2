# Sistema de Gestión de Usuarios y Roles (ACL)

## ✅ Implementación Completada

Se ha implementado exitosamente el sistema de gestión de usuarios y roles para el TPV con las siguientes características:

## 📁 Archivos Creados/Modificados

### 1. **Enum UserRole** 
📄 `app/Enums/UserRole.php`
- Dos roles: `ADMIN` (admin) y `WAITER` (waiter)
- Métodos `getLabel()` para etiquetas en español
- Métodos `getColor()` para badges en Filament (Admin: danger/rojo, Waiter: success/verde)

### 2. **Migración**
📄 `database/migrations/2026_01_20_135749_add_role_to_users_table.php`
- Añade columna `role` (string) a la tabla `users`
- Valor por defecto: 'waiter'
- ✅ Migración ejecutada exitosamente

### 3. **Modelo User Actualizado**
📄 `app/Models/User.php`
- ✅ Cast `'role' => UserRole::class`
- ✅ Implementa interfaz `FilamentUser`
- ✅ Método `canAccessPanel()`: Solo ADMIN puede acceder al panel `/admin`
- ✅ Relación `hasMany` con `Order`
- ✅ Relación `hasMany` con `StockMovement`
- Campo `role` añadido a `$fillable`

### 4. **UserResource para Filament**
📄 `app/Filament/Resources/Users/UserResource.php`
- Navegación: "Empleados" en grupo "Administración"
- Icono: `heroicon-o-users`
- Ordenamiento: Prioridad 1

#### Formulario (UserForm.php)
📄 `app/Filament/Resources/Users/Schemas/UserForm.php`
- ✅ Campo: Nombre (requerido)
- ✅ Campo: Email (requerido, único, validado)
- ✅ Campo: Rol (Select con opciones ADMIN/WAITER)
- ✅ Campo: Contraseña
  - Solo requerida en creación (`required(fn (string $context): bool => $context === 'create')`)
  - Hash automático al guardar
  - Dehydrated solo si tiene valor (`dehydrated(fn ($state) => filled($state))`)
  - Permite editar usuarios sin cambiar contraseña
- Campo: Email verificado (con fecha actual por defecto)

#### Tabla (UsersTable.php)
📄 `app/Filament/Resources/Users/Tables/UsersTable.php`
- ✅ Columna: Nombre (searchable, sortable)
- ✅ Columna: Email (searchable, sortable, copyable)
- ✅ Columna: Rol (badge con colores, sortable)
- ✅ Columna: Fecha de creación (toggleable, oculta por defecto)
- ✅ Columna: Última actualización (toggleable, oculta por defecto)
- ✅ Filtro: Por rol
- Acciones: Editar, Eliminar

#### Páginas
- 📄 `app/Filament/Resources/Users/Pages/CreateUser.php` - Redirige a lista tras crear
- 📄 `app/Filament/Resources/Users/Pages/EditUser.php` - Redirige a lista tras editar
- 📄 `app/Filament/Resources/Users/Pages/ListUsers.php` - Listado de empleados

### 5. **UserPolicy**
📄 `app/Policies/UserPolicy.php`

#### Permisos implementados:
- ✅ `viewAny()`: Solo ADMIN
- ✅ `view()`: Solo ADMIN
- ✅ `create()`: Solo ADMIN
- ✅ `update()`: Solo ADMIN
- ✅ `delete()`: Solo ADMIN + **NO puede borrarse a sí mismo**
- ✅ `restore()`: Solo ADMIN
- ✅ `forceDelete()`: Solo ADMIN + **NO puede borrarse a sí mismo**
- ✅ `deleteAny()`: Solo ADMIN
- ✅ `restoreAny()`: Solo ADMIN
- ✅ `forceDeleteAny()`: Solo ADMIN

#### Registro de Policy
📄 `app/Providers/AppServiceProvider.php`
- ✅ Policy registrada en `Gate::policy(User::class, UserPolicy::class)`

### 6. **UserSeeder Actualizado**
📄 `database/seeders/UserSeeder.php`
- Usuario Admin: `admin@admin.com` con rol `ADMIN`
- Camareros: Carlos, María, Juan con rol `WAITER`
- ✅ Roles actualizados en usuarios existentes

### 7. **Página de Perfil de Usuario**
📄 `app/Filament/Pages/EditProfile.php`
📄 `resources/views/filament/pages/edit-profile.blade.php`
- ✅ Cada usuario puede editar su **correo electrónico**
- ✅ Cada usuario puede cambiar su **contraseña**
- ✅ Validación de contraseña actual antes de cambiarla
- ✅ Confirmación de nueva contraseña
- ❌ **Nombre bloqueado** (solo lectura)
- ❌ **Rol bloqueado** (no se muestra)
- Navegación: "Mi Perfil" en el menú principal
- Icono: `heroicon-o-user-circle`

## 🔐 Lógica de Acceso

### Panel Admin (`/admin`)
- ✅ Solo usuarios con rol `ADMIN` pueden acceder
- Los `WAITER` son **rechazados automáticamente** por `canAccessPanel()`
- Mensaje de error al intentar acceder sin permisos

### Gestión de Usuarios
- Solo administradores (`ADMIN`) pueden:
  - Ver la lista de empleados
  - Crear nuevos empleados
  - Editar empleados existentes
  - Eliminar empleados (excepto a sí mismos)

### TPV (`/pos`)
- Los camareros (`WAITER`) deben usar esta interfaz
- No tienen acceso al panel administrativo

## 🧪 Pruebas de Funcionalidad

### Credenciales de Prueba

**Administrador:**
- Email: `admin@admin.com`
- Password: `password`
- Rol: ADMIN
- ✅ Puede acceder a `/admin`
- ✅ Puede gestionar empleados

**Camareros:**
- Carlos: `carlos@restaurant.com` / `password`
- María: `maria@restaurant.com` / `password`
- Juan: `juan@restaurant.com` / `password`
- Rol: WAITER
- ❌ NO pueden acceder a `/admin`
- ✅ Solo pueden usar `/pos`

## 📋 Características Destacadas

### ✨ Seguridad
1. **Protección a nivel de modelo**: `FilamentUser` controla acceso al panel
2. **Protección a nivel de recurso**: `UserPolicy` controla operaciones CRUD
3. **Auto-protección**: Admin no puede borrarse a sí mismo
4. **Hash automático**: Contraseñas hasheadas con bcrypt

### ✨ UX/UI
1. **Edición sin contraseña**: Puedes editar un usuario sin tener que reescribir su contraseña
2. **Perfil de usuario**: Cada usuario puede editar su email y contraseña desde "Mi Perfil"
3. **Badges de colores**: Rol ADMIN en rojo (danger), WAITER en verde (success)
4. **Búsqueda**: Por nombre y email
5. **Filtros**: Por rol
6. **Redirecciones**: Tras crear/editar vuelve a la lista
7. **Traducciones**: Todas las etiquetas en español

### ✨ Escalabilidad
1. **Enum para roles**: Fácil añadir nuevos roles en el futuro
2. **Policy completa**: Todos los métodos implementados
3. **Relaciones definidas**: User -> Orders, User -> StockMovements

## 🚀 Próximos Pasos Sugeridos

1. **Implementar interfaz TPV** (`/pos`) para camareros
2. **Middleware para rutas TPV**: Asegurar que solo WAITER acceda
3. **Auditoría**: Registrar quién creó/modificó cada pedido/movimiento
4. **Dashboard por rol**: Mostrar estadísticas según el rol del usuario

## 📝 Notas Técnicas

- Laravel 11 con Filament 5
- Base de datos: PostgreSQL (basado en los errores mostrados)
- UUID para IDs de usuario
- Dehydration condicional en password para edición segura
- Navigation group con tipo `\UnitEnum|string|null` (Filament 5)

---

**Implementado por:** GitHub Copilot  
**Fecha:** 20/01/2026  
**Estado:** ✅ COMPLETADO Y FUNCIONAL
