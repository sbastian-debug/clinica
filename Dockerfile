FROM php:8.2-apache

# Instalar driver PostgreSQL (pdo_pgsql) para conectarse a Neon Cloud
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copiar el código fuente al servidor Apache del contenedor
COPY . /var/www/html/

# Dar permisos y exponer puerto 80
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80