FROM php:8.5-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/demo/public

# 1. Instalar dependencias
# He añadido 'libpq-dev' (necesario para Postgres)
RUN apt-get update && apt-get install -y \
    git unzip zip curl wget gnupg libzip-dev libicu-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install zip intl pdo pdo_pgsql pdo_mysql

# 2. 🪄 EL TRUCO DE PERMISOS
# Cambiamos el ID de www-data a 1000 para que coincida con tu usuario WSL
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# 3. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Configurar Apache
RUN a2enmod rewrite
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Configurar Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html/demo

ENTRYPOINT ["/entrypoint.sh"]
# Comando por defecto para arrancar Apache en primer plano
CMD ["apache2-foreground"]