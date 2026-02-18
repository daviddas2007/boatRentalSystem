# ============================================
# Stage 1: Build Frontend (Vue 3 + Vite)
# ============================================
FROM node:20-alpine AS frontend-build

WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./

# Install Node dependencies
RUN npm install

# Copy frontend source files
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ ./resources/

# Build Vue 3 frontend
RUN npm run build

# ============================================
# Stage 2: PHP Application (no Apache)
# ============================================
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for Docker cache)
COPY composer.json composer.lock* ./

# Install PHP dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the application
COPY . .

# Create an empty .env file (needed by Laravel artisan commands)
RUN touch .env

# Run composer scripts after full copy
RUN composer dump-autoload --optimize

# Copy built frontend assets from Stage 1
COPY --from=frontend-build /app/public/build ./public/build

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Create storage link
RUN php artisan storage:link || true

# Startup script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
