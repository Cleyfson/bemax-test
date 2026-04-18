#!/bin/sh
set -e

# Fix storage and cache permissions when volume is mounted over the image
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
