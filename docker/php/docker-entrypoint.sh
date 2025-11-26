#!/bin/sh
set -e

# Laravel requires write permissions on storage and bootstrap/cache directories
# This script runs when the container starts to set proper permissions
# It runs as root so it can change ownership

# Set ownership for Laravel directories (if they exist)
if [ -d "/var/www/storage" ]; then
    chown -R moneyflow:www-data /var/www/storage
    chmod -R 775 /var/www/storage
fi

if [ -d "/var/www/bootstrap/cache" ]; then
    chown -R moneyflow:www-data /var/www/bootstrap/cache
    chmod -R 775 /var/www/bootstrap/cache
fi

# Execute the command (php-fpm)
# PHP-FPM master process runs as root (normal and safe)
# Worker processes will run as moneyflow user (configured in www.conf)
exec "$@"

