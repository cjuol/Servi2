# Instrucciones para la funcionalidad de Avatar

## Migración de Base de Datos

Ejecuta la migración para añadir el campo `avatar` a la tabla `users`:

```bash
docker compose exec app php artisan migrate
```

## Enlace Simbólico de Storage

Si aún no lo has hecho, crea el enlace simbólico para que las imágenes sean accesibles públicamente:

```bash
docker compose exec app php artisan storage:link
```

Este comando crea un enlace simbólico desde `public/storage` hacia `storage/app/public`, permitiendo que los avatares subidos sean accesibles vía web.

## Funcionamiento

- Los usuarios pueden subir su foto de perfil desde el modal de edición de perfil
- La imagen se guarda en `storage/app/public/avatars/`
- Si el usuario tiene un avatar, se mostrará en la esquina superior derecha
- Si no tiene avatar, se mostrará la inicial de su nombre (comportamiento por defecto de Filament)
- Las imágenes tienen un límite de 2MB
- Se incluye un editor de imágenes con recorte circular para un mejor ajuste
