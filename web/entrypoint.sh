#!/bin/bash
set -e

PROJECT_DIR="/var/www/html/demo"
FRAMEWORK="${FRAMEWORK:-none}"

# Permitir ejecutar composer como superusuario sin advertencias
export COMPOSER_ALLOW_SUPERUSER=1

# -----------------------------------------------------
# Lógica Symfony
# -----------------------------------------------------
if [ "$FRAMEWORK" = "symfony" ] && [ ! -f "$PROJECT_DIR/composer.json" ]; then
  echo "⚙️  Creando proyecto Symfony en $PROJECT_DIR..."
  # Usamos user 'root' temporalmente, luego el chown lo arregla
  composer create-project symfony/skeleton:"6.4.*" "$PROJECT_DIR" --no-interaction --prefer-dist
  
  # Ajuste de permisos extra para carpetas de caché/logs de Symfony
  chmod -R 777 "$PROJECT_DIR/var" 2>/dev/null || true
  
  echo "✅ Proyecto Symfony creado correctamente."

elif [ "$FRAMEWORK" = "symfony" ]; then
  echo "✅ Proyecto Symfony detectado, omitiendo instalación."
fi

# -----------------------------------------------------
# Lógica Laravel
# -----------------------------------------------------
if [ "$FRAMEWORK" = "laravel" ] && [ ! -f "$PROJECT_DIR/composer.json" ]; then
  echo "⚙️  Creando proyecto Laravel en $PROJECT_DIR..."
  composer create-project laravel/laravel "$PROJECT_DIR" --no-interaction --prefer-dist
  
  echo "⚙️  Instalando Livewire..."
  cd "$PROJECT_DIR" && composer require livewire/livewire
  
  echo "⚙️  Instalando Filament..."
  cd "$PROJECT_DIR" && composer require filament/filament:"^3.0"
  
  # OJO: Filament requiere instalar el panel. 
  # Intentamos instalarlo automáticamente sin interacción.
  # Si falla, tendrás que ejecutarlo manualmente: php artisan filament:install --panels
  echo "⚙️  Configurando Panel de Filament..."
  cd "$PROJECT_DIR" && php artisan filament:install --panels --no-interaction --force || echo "⚠️ Debes configurar el panel manualmente."

  echo "✅ Laravel con Filament instalado."

elif [ "$FRAMEWORK" = "laravel" ]; then
  echo "✅ Proyecto Laravel detectado, omitiendo instalación."
fi

# -----------------------------------------------------
# ARREGLO FINAL DE PERMISOS
# -----------------------------------------------------
# Esto asigna los archivos al usuario www-data (que es UID 1000 gracias al Dockerfile)
# El "|| true" evita que el contenedor muera si hay un error de permisos raro.
if [ -d "$PROJECT_DIR" ]; then
    echo "🔧 Asignando permisos a www-data (UID 1000)..."
    chown -R www-data:www-data "$PROJECT_DIR" || true
fi

# Ejecutamos Apache
echo "🚀 Arrancando Apache..."
exec apache2-foreground