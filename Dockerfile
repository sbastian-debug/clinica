FROM php:8.2-apache

# 1. Instalar dependencias del sistema y extensiones de PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# 2. Instalar Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configurar Apache para que la raíz pública sea la carpeta public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Habilitar mod_rewrite de Apache para redirecciones limpias
RUN a2enmod rewrite

# 5. Copiar los archivos del proyecto al contenedor
WORKDIR /var/www/html
COPY . .

# 6. Instalar librerías de Composer si existe composer.json
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

# 7. Asignar permisos al usuario de Apache (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && if [ -d storage ]; then chmod -R 775 storage bootstrap/cache; fi

EXPOSE 80