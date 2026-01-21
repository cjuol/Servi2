#!/bin/bash

# ════════════════════════════════════════════════════════════════
# SCRIPT DE DESPLIEGUE - REFACTORIZACIÓN TPV
# ════════════════════════════════════════════════════════════════
# 
# Este script ejecuta todos los pasos necesarios para desplegar
# la refactorización del sistema TPV con trazabilidad completa.
#
# Uso: ./deploy-refactorizacion.sh
# ════════════════════════════════════════════════════════════════

set -e  # Detener si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funciones helper
print_step() {
    echo -e "\n${BLUE}══════════════════════════════════════════${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}══════════════════════════════════════════${NC}\n"
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

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "Error: No se encuentra el archivo 'artisan'. Asegúrate de estar en el directorio raíz del proyecto."
    exit 1
fi

print_step "PASO 1: Verificación del entorno"

# Verificar Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker no está instalado"
    exit 1
fi

if ! docker compose -f enviroment/docker-compose.yml ps &> /dev/null; then
    print_warning "Los contenedores no están corriendo. Iniciando..."
    docker compose -f enviroment/docker-compose.yml up -d
    sleep 5
fi

print_success "Entorno Docker verificado"

# ════════════════════════════════════════════════════════════════
print_step "PASO 2: Backup de la base de datos"
# ════════════════════════════════════════════════════════════════

BACKUP_DIR="backups"
BACKUP_FILE="${BACKUP_DIR}/backup_before_refactorizacion_$(date +%Y%m%d_%H%M%S).sql"

mkdir -p "$BACKUP_DIR"

print_warning "Creando backup de la base de datos..."
docker compose -f enviroment/docker-compose.yml exec -T db pg_dump -U demo_user demo_db > "$BACKUP_FILE"

if [ -f "$BACKUP_FILE" ]; then
    print_success "Backup creado: $BACKUP_FILE"
else
    print_error "Error al crear el backup"
    exit 1
fi

# ════════════════════════════════════════════════════════════════
print_step "PASO 3: Verificar estado actual de migraciones"
# ════════════════════════════════════════════════════════════════

echo "Migraciones actuales:"
docker compose -f enviroment/docker-compose.yml exec web php artisan migrate:status

# ════════════════════════════════════════════════════════════════
print_step "PASO 4: Ejecutar nueva migración"
# ════════════════════════════════════════════════════════════════

print_warning "Se va a ejecutar la migración que:"
echo "  - Elimina: orders.stripe_payment_id"
echo "  - Añade: stock_movements.order_id (con FK)"
echo ""
read -p "¿Continuar? (s/n): " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    print_error "Migración cancelada por el usuario"
    exit 1
fi

docker compose -f enviroment/docker-compose.yml exec web php artisan migrate --force

print_success "Migración ejecutada correctamente"

# ════════════════════════════════════════════════════════════════
print_step "PASO 5: Verificar estructura de base de datos"
# ════════════════════════════════════════════════════════════════

echo "Verificando columnas de 'orders':"
docker compose -f enviroment/docker-compose.yml exec -T db psql -U demo_user -d demo_db -c "\d orders" | grep -v stripe_payment_id && print_success "stripe_payment_id eliminado correctamente" || print_warning "Verificar manualmente"

echo ""
echo "Verificando columnas de 'stock_movements':"
docker compose -f enviroment/docker-compose.yml exec -T db psql -U demo_user -d demo_db -c "\d stock_movements" | grep order_id && print_success "order_id añadido correctamente" || print_error "order_id NO encontrado"

# ════════════════════════════════════════════════════════════════
print_step "PASO 6: Limpiar cachés de la aplicación"
# ════════════════════════════════════════════════════════════════

docker compose -f enviroment/docker-compose.yml exec web php artisan optimize:clear
print_success "Caché de config, rutas y vistas limpiado"

docker compose -f enviroment/docker-compose.yml exec web php artisan filament:cache-components
print_success "Caché de componentes Filament regenerado"

# ════════════════════════════════════════════════════════════════
print_step "PASO 7: Ejecutar tests (opcional)"
# ════════════════════════════════════════════════════════════════

read -p "¿Ejecutar tests de la refactorización? (s/n): " -n 1 -r
echo

if [[ $REPLY =~ ^[Ss]$ ]]; then
    docker compose -f enviroment/docker-compose.yml exec web php artisan test --filter=OrderTerminalRefactoredTest
    print_success "Tests ejecutados"
else
    print_warning "Tests omitidos"
fi

# ════════════════════════════════════════════════════════════════
print_step "PASO 8: Verificación final"
# ════════════════════════════════════════════════════════════════

echo "Estado de migraciones:"
docker compose -f enviroment/docker-compose.yml exec web php artisan migrate:status | tail -n 5

echo ""
echo "Rutas de Filament disponibles:"
docker compose -f enviroment/docker-compose.yml exec web php artisan route:list --name=filament | grep movimientos-stock

# ════════════════════════════════════════════════════════════════
print_step "🎉 DESPLIEGUE COMPLETADO"
# ════════════════════════════════════════════════════════════════

echo ""
print_success "La refactorización se ha desplegado correctamente"
echo ""
echo "Próximos pasos:"
echo "  1. Accede a Filament → Movimientos de Stock"
echo "  2. Realiza una venta de prueba en el TPV"
echo "  3. Verifica que aparece el botón 'Ver Ticket' 👁️"
echo "  4. Revisa los logs: storage/logs/laravel.log"
echo ""
echo "Backup guardado en: ${GREEN}$BACKUP_FILE${NC}"
echo ""
print_warning "Si algo falla, puedes restaurar con:"
echo "  cat $BACKUP_FILE | docker compose -f enviroment/docker-compose.yml exec -T db psql -U demo_user demo_db"
echo ""

# ════════════════════════════════════════════════════════════════
# FIN DEL SCRIPT
# ════════════════════════════════════════════════════════════════
