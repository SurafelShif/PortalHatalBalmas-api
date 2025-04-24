# Use the official PHP image with Apache
FROM php:8.2-apache
# Set working directory to Laravel root
WORKDIR /var/www/html
# Install system dependencies
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libx11-xcb1 \
    libxcomposite1 \
    libasound2 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libxcb1 \
    libxcb1-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    zip \
    unzip \
    libzip-dev \
    git \
    vim \
    iputils-ping \
    curl \
    sudo \
    net-tools && \
    rm -rf /var/lib/apt/lists/*
# Explicitly set PHP memory limit
RUN sed -i 's/memory_limit = .*/memory_limit = 256M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/memory_limit = .*/memory_limit = 256M/' /usr/local/etc/php/php.ini-production && \
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /usr/local/etc/php/php.ini-production && \
    sed -i 's/post_max_size = .*/post_max_size = 128M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/post_max_size = .*/post_max_size = 128M/' /usr/local/etc/php/php.ini-production && \
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
# Configure GD with additional support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd zip
# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Modify the default Apache port from 80 to 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
# Modify the default Apache configuration to point to Laravel's public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
# Enable mod_rewrite for Apache
RUN a2enmod rewrite
# Copy the existing Laravel project into the container
COPY . /var/www/html/
# Workaround to allow composer install as a superUser
ENV COMPOSER_ALLOW_SUPERUSER=1
# Run Composer install for Laravel project dependencies
RUN composer install --ignore-platform-reqs
# Create a user with /var/www/html as the home directory and set HOME environment variable
RUN useradd -d /var/www/html -ms /bin/bash username && \
    echo "username ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers && \
    chown -R username:username /var/www/html && \
    chmod -R 777 /var/www/html && \
    export HOME=/var/www/html
# Grant permissions for Laravel's storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache
# Additionally, set the permissions for the directories
RUN chmod -R 775 storage bootstrap/cache
# Set permissions for storage/logs and ensure they are writable by the web server
RUN chown -R www-data:www-data /var/www/html/storage/logs && \
    chmod -R 775 /var/www/html/storage/logs
# Set permissions for the entire storage directory
RUN chmod -R 775 /var/www/html/storage && \
    chown -R www-data:www-data /var/www/html/storage
# Add the storage:link command
RUN php artisan storage:link
# Execute the command to clear Laravel log file
RUN echo "" > storage/logs/laravel.log
# Expose port 8080
EXPOSE 8080
# Add www-data to sudoers
RUN echo 'www-data ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
# Set password for www-data
RUN echo 'www-data:admin' | chpasswd
# Switch to user www-data for subsequent commands and container runtime
USER www-data

