#!/bin/bash

# Script para configurar el sistema de pagos del TPV

echo "🚀 Configurando Sistema de Pagos TPV..."
echo ""

# 1. Instalar Stripe SDK
echo "📦 1/5 - Instalando Stripe SDK..."
composer require stripe/stripe-php --quiet
echo "✅ Stripe SDK instalado"
echo ""

# 2. Ejecutar migraciones
echo "🗄️  2/5 - Ejecutando migraciones..."
php artisan migrate --force
echo "✅ Migraciones completadas"
echo ""

# 3. Limpiar caché
echo "🧹 3/5 - Limpiando caché..."
php artisan config:clear
php artisan cache:clear
echo "✅ Caché limpiada"
echo ""

# 4. Verificar configuración
echo "⚙️  4/5 - Verificando configuración..."

if grep -q "STRIPE_KEY" .env; then
    echo "✅ Variables STRIPE encontradas en .env"
else
    echo "⚠️  ADVERTENCIA: No se encontraron variables STRIPE en .env"
    echo ""
    echo "📝 Añade estas líneas a tu archivo .env:"
    echo ""
    cat .env.stripe.example
    echo ""
fi

# 5. Resumen
echo "📊 5/5 - Resumen de la instalación:"
echo ""
echo "✅ Backend implementado:"
echo "   - Lógica de pedidos con transacciones DB"
echo "   - Integración con Stripe PaymentIntent"
echo "   - Control de stock atómico"
echo ""
echo "✅ Frontend implementado:"
echo "   - Modal de pago con Stripe Elements"
echo "   - Procesamiento de pagos en efectivo y tarjeta"
echo "   - Auto-impresión de tickets térmicos"
echo ""
echo "✅ Archivos creados:"
echo "   - resources/views/pos/ticket.blade.php"
echo "   - database/migrations/*_add_payment_fields_to_orders_table.php"
echo "   - config/services.php (actualizado)"
echo ""
echo "📖 Lee PAYMENT_SYSTEM_SETUP.md para más información"
echo ""
echo "🎉 ¡Sistema de pagos listo!"
echo ""
echo "⚠️  IMPORTANTE: Configura tus claves de Stripe en el archivo .env antes de usar el sistema"
echo ""
