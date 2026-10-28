#!/bin/sh
set -e

# Configurar el puerto dinámico de Render ($PORT por defecto 10000 o 80)
PORT=${PORT:-10000}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Si no se configuró base de datos externa, inicializar SQLite automáticamente
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Generar APP_KEY automáticamente si no fue configurada en Render
if [ -z "$APP_KEY" ]; then
    echo "Generando APP_KEY automática para producción..."
    php artisan key:generate --force || true
fi

# Crear enlace simbólico de almacenamiento para imágenes
php artisan storage:link --force || true

# Ejecutar migraciones y seeders automáticos (para tener catálogo y roles listos)
echo "Ejecutando migraciones y seeders en la base de datos..."
php artisan migrate --force || echo "Aviso: Error durante migrate"
php artisan db:seed --force || echo "Aviso: Error durante seed"

# Ajustar permisos estrictos para el usuario www-data de Apache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Optimizar cachés de Laravel
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Servidor Portátiles Perú listo. Iniciando Apache en el puerto ${PORT}..."
exec apache2-foreground
