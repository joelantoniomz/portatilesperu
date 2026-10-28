#!/bin/sh
set -e

# Configurar el puerto dinámico de Render ($PORT por defecto 10000 o 80)
PORT=${PORT:-10000}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Crear enlace simbólico de almacenamiento
php artisan storage:link --force || true

# Limpiar y optimizar cachés de Laravel
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Ejecutar migraciones si la base de datos está configurada
if [ -n "$DB_HOST" ] || [ "$DB_CONNECTION" = "sqlite" ] || [ -n "$DATABASE_URL" ]; then
    echo "Ejecutando migraciones de base de datos..."
    php artisan migrate --force || echo "Aviso: No se pudieron ejecutar las migraciones en este arranque."
fi

echo "Iniciando servidor Apache en el puerto ${PORT}..."
exec apache2-foreground
