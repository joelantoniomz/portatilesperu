# ==============================================================================
# ETAPA 1: Construcción de Assets Frontend (Vite + Tailwind CSS)
# ==============================================================================
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ==============================================================================
# ETAPA 2: Runtime de Producción (PHP 8.3 + Apache)
# ==============================================================================
FROM php:8.3-apache

# Actualizar e instalar dependencias del sistema y extensiones de PHP requeridas
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    gd \
    zip \
    bcmath \
    mbstring \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite de Apache para rutas de Laravel
RUN a2enmod rewrite

# Configurar Apache para apuntar a la carpeta public de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiar el proyecto
COPY . .

# Copiar los assets compilados de la Etapa 1
COPY --from=frontend /app/public/build ./public/build

# Instalar dependencias PHP de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos para storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de entrada para Render
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Puerto estándar de Render (se adapta automáticamente a la variable $PORT)
EXPOSE 80 10000

ENTRYPOINT ["entrypoint.sh"]
