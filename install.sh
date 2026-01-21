#!/bin/bash

# ========================================
# 🚀 Script de Instalación del Sistema de Pagos TPV
# ========================================

set -e  # Salir si hay errores

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║   🚀 Sistema de Pagos TPV - Instalación Automática        ║"
echo "║   Laravel 11 + Livewire 5 + Stripe                        ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir pasos
print_step() {
    echo ""
    echo -e "${BLUE}▶ $1${NC}"
    echo "─────────────────────────────────────────────────────────"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# ========================================
# PASO 1: Verificar Requisitos
# ========================================
print_step "1/6 - Verificando requisitos del sistema"

# Verificar PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    print_success "PHP $PHP_VERSION instalado"
else
    print_error "PHP no está instalado"
    exit 1
fi

# Verificar Composer
if command -v composer &> /dev/null; then
    print_success "Composer instalado"
else
    print_error "Composer no está instalado"
    exit 1
fi

# Verificar que estamos en un proyecto Laravel
if [ ! -f "artisan" ]; then
    print_error "Este script debe ejecutarse desde la raíz de un proyecto Laravel"
    exit 1
fi
print_success "Proyecto Laravel detectado"

# ========================================
# PASO 2: Instalar Dependencias
# ========================================
print_step "2/6 - Instalando Stripe SDK"

if composer show | grep -q "stripe/stripe-php"; then
    print_warning "Stripe SDK ya está instalado"
else
    composer require stripe/stripe-php --no-interaction
    print_success "Stripe SDK instalado correctamente"
fi

# ========================================
# PASO 3: Verificar Archivos
# ========================================
print_step "3/6 - Verificando archivos del sistema"

FILES_TO_CHECK=(
    "app/Livewire/Pos/OrderTerminal.php"
    "app/Models/Order.php"
    "resources/views/livewire/pos/order-terminal.blade.php"
    "resources/views/pos/ticket.blade.php"
    "database/migrations/2026_01_21_120000_add_payment_fields_to_orders_table.php"
    "config/services.php"
)

MISSING_FILES=0
for file in "${FILES_TO_CHECK[@]}"; do
    if [ -f "$file" ]; then
        print_success "Encontrado: $file"
    else
        print_error "Falta: $file"
        MISSING_FILES=$((MISSING_FILES + 1))
    fi
done

if [ $MISSING_FILES -gt 0 ]; then
    print_error "Faltan $MISSING_FILES archivos críticos"
    exit 1
fi

# ========================================
# PASO 4: Configurar Variables de Entorno
# ========================================
print_step "4/6 - Verificando configuración de Stripe"

if grep -q "STRIPE_KEY" .env; then
    STRIPE_KEY=$(grep STRIPE_KEY .env | cut -d '=' -f2)
    if [ "$STRIPE_KEY" == "pk_test_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" ] || [ -z "$STRIPE_KEY" ]; then
        print_warning "Variables STRIPE encontradas pero no configuradas"
        echo ""
        echo "⚠️  ACCIÓN REQUERIDA:"
        echo "1. Ve a: https://dashboard.stripe.com/apikeys"
        echo "2. Copia tus claves de API"
        echo "3. Edita el archivo .env:"
        echo ""
        echo "   STRIPE_KEY=pk_test_TU_CLAVE_AQUI"
        echo "   STRIPE_SECRET=sk_test_TU_SECRETO_AQUI"
        echo ""
    else
        print_success "Variables STRIPE configuradas"
    fi
else
    print_warning "Variables STRIPE no encontradas en .env"
    echo ""
    echo "Añadiendo variables de ejemplo..."
    echo "" >> .env
    echo "# Stripe Configuration" >> .env
    echo "STRIPE_KEY=pk_test_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" >> .env
    echo "STRIPE_SECRET=sk_test_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" >> .env
    
    print_warning "Variables añadidas. Por favor, configúralas antes de usar el sistema."
fi

# ========================================
# PASO 5: Ejecutar Migraciones
# ========================================
print_step "5/6 - Ejecutando migraciones"

echo "¿Deseas ejecutar las migraciones ahora? (s/n)"
read -r RESPONSE

if [[ "$RESPONSE" =~ ^[Ss]$ ]]; then
    php artisan migrate --force
    print_success "Migraciones ejecutadas correctamente"
else
    print_warning "Migraciones omitidas. Ejecuta 'php artisan migrate' manualmente."
fi

# ========================================
# PASO 6: Limpiar Caché
# ========================================
print_step "6/6 - Limpiando caché"

php artisan config:clear > /dev/null 2>&1
print_success "Caché de configuración limpiada"

php artisan cache:clear > /dev/null 2>&1
print_success "Caché de aplicación limpiada"

php artisan view:clear > /dev/null 2>&1
print_success "Caché de vistas limpiada"

# ========================================
# RESUMEN FINAL
# ========================================
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║   ✅ Instalación Completada                                ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

print_success "Sistema de Pagos TPV instalado correctamente"
echo ""
echo "📊 RESUMEN:"
echo "  ✅ Stripe SDK instalado"
echo "  ✅ Archivos verificados"
echo "  ✅ Caché limpiada"
echo ""

# Verificar si las claves están configuradas
if grep -q "STRIPE_KEY=pk_test_X" .env || grep -q "STRIPE_KEY=$" .env; then
    echo "⚠️  IMPORTANTE: Configura tus claves de Stripe antes de usar:"
    echo ""
    echo "  1. Edita el archivo .env"
    echo "  2. Reemplaza STRIPE_KEY y STRIPE_SECRET con tus claves reales"
    echo "  3. Ejecuta: php artisan config:clear"
    echo ""
else
    echo "✅ Sistema listo para usar"
    echo ""
fi

echo "📖 DOCUMENTACIÓN:"
echo "  - Guía de uso:       PAYMENT_SYSTEM_SETUP.md"
echo "  - Arquitectura:      ARCHITECTURE.md"
echo "  - Ejemplos:          EXAMPLES.md"
echo "  - Resumen:           IMPLEMENTATION_SUMMARY.md"
echo ""

echo "🧪 TESTING:"
echo "  Tarjeta de prueba: 4242 4242 4242 4242"
echo "  Fecha: Cualquier futura (ej: 12/28)"
echo "  CVV: 123"
echo ""

echo "🚀 Para probar el sistema:"
echo "  php artisan serve"
echo "  Luego visita: http://localhost:8000/pos"
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║   🎉 ¡Disfruta del sistema de pagos!                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
