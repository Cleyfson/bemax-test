#!/bin/sh
set -e

# Install composer dependencies if vendor/ is missing (fresh clone scenario)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Fix storage and cache permissions when volume is mounted over the image
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
