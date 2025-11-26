# Step 1: Start with official PHP 8.3 FPM image
# FPM (FastCGI Process Manager) is what Nginx uses to run PHP code
FROM php:8.3-fpm

# Step 2: Install system dependencies needed for PHP extensions
# These packages are required to compile PHP extensions later
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Step 3: Install PHP extensions required by Laravel
# 
# mysqli & pdo_mysql: Database connectivity for MySQL
# bcmath: Arbitrary precision mathematics (used by Laravel)
# gd: Image manipulation library
# zip: ZIP archive support
# xml: XML parsing support
# mbstring: Multi-byte string handling (for internationalization)
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip xml

# Step 4: Install Composer (PHP dependency manager)
# Composer is like npm for PHP - it installs Laravel and packages
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Step 5: Set working directory to Laravel's root
WORKDIR /var/www

# Step 6: Create a non-root user for security
# Running as root is dangerous - if someone hacks your app, they'd have root access
RUN useradd -G www-data,root -u 1000 -d /home/moneyflow moneyflow
RUN mkdir -p /home/moneyflow/.composer && \
    chown -R moneyflow:www-data /home/moneyflow

# Step 7: Install gosu (allows running commands as different user)
# This is needed by the entrypoint script to switch from root to moneyflow user
# gosu is better than su because it handles process signals correctly
RUN apt-get update && apt-get install -y \
    wget \
    && wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/1.17/gosu-$(dpkg --print-architecture)" \
    && chmod +x /usr/local/bin/gosu \
    && apt-get remove -y wget \
    && rm -rf /var/lib/apt/lists/*

# Step 8: Copy PHP-FPM pool configuration
# This configures PHP-FPM to run worker processes as moneyflow user
# The master process stays as root (normal and safe for PHP-FPM)
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Step 9: Copy entrypoint script and make it executable
# This script sets proper permissions when container starts (after volumes are mounted)
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Step 10: Set entrypoint script
# This runs every time the container starts to ensure permissions are correct
# PHP-FPM will handle user switching via pool configuration (www.conf)
ENTRYPOINT ["docker-entrypoint.sh"]

# Step 11: Expose port 9000 (PHP-FPM default port)
# Nginx will connect to this port to execute PHP code
EXPOSE 9000

# Step 12: Start PHP-FPM when container starts
# PHP-FPM master runs as root, workers run as moneyflow (configured in www.conf)
CMD ["php-fpm"]

