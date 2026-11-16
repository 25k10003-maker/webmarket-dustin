#!/bin/bash
set -e

# Adapt Apache to dynamic PORT environment variable (Render / Railway default)
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/*.conf

# Clear and cache configurations
php artisan config:clear || true
php artisan cache:clear || true

# Run database migrations and seed automatically if requested
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running Laravel database migrations and seeding..."
    php artisan migrate --force --seed || echo "Migration encountered an issue, proceeding to start server..."
fi

# Optimize Laravel for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache web server on port $PORT..."
exec apache2-foreground
