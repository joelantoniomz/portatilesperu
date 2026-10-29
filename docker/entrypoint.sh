#!/bin/sh
set -e

# Configurar el puerto dinámico de Render ($PORT por defecto 10000 o 80)
PORT=${PORT:-10000}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Asegurar existencia de archivo .env para Laravel
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# Detectar URL pública en Render
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
elif [ -z "$APP_URL" ]; then
    export APP_URL="https://portatilesperu.onrender.com"
fi

export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-false}

# Si no se configuró base de datos externa, inicializar SQLite automáticamente
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database

    sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|" /var/www/html/.env 2>/dev/null || true
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|" /var/www/html/.env 2>/dev/null || true
fi

# Gestionar APP_KEY de forma persistente
if [ -n "$APP_KEY" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env 2>/dev/null || echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
else
    if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
        echo "Generando APP_KEY automática para producción..."
        php artisan key:generate --force || true
    fi
    ENV_KEY=$(grep "^APP_KEY=" /var/www/html/.env | head -n1 | cut -d '=' -f2-)
    if [ -n "$ENV_KEY" ]; then
        export APP_KEY="$ENV_KEY"
    fi
fi

# Configurar Apache para propagar variables a PHP mod_php
cat <<EOF > /etc/apache2/conf-available/laravel-env.conf
PassEnv APP_ENV
PassEnv APP_KEY
PassEnv APP_DEBUG
PassEnv APP_URL
PassEnv DB_CONNECTION
PassEnv DB_DATABASE
PassEnv PORT
EOF
a2enconf laravel-env 2>/dev/null || true

# Crear enlace simbólico de almacenamiento para imágenes
php artisan storage:link --force || true

# Ejecutar migraciones y seeders automáticos (para tener catálogo y roles listos)
echo "Ejecutando migraciones y seeders en la base de datos..."
php artisan migrate --force || echo "Aviso: Error durante migrate"
php artisan db:seed --force || echo "Aviso: Error durante seed"

# Ajustar permisos estrictos para el usuario www-data de Apache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 644 /var/www/html/.env

# Optimizar cachés de Laravel
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Servidor Portátiles Perú listo. Iniciando Apache en el puerto ${PORT}..."
exec apache2-foreground
