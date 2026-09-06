# ==============================================================================
# Stage 1: Build Frontend Assets (Node.js & Vite)
# ==============================================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Cache dependencies layer
COPY package.json package-lock.json ./
RUN npm ci

# Copy application files and build Vite bundle
COPY . .
RUN npm run build

# ==============================================================================
# Stage 2: Production PHP-FPM + Nginx Container (ServerSideUp)
# ==============================================================================
FROM serversideup/php:8.3-fpm-nginx

# ServerSideUp Laravel production automations
ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_FORCE=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true \
    SSL_MODE=off

WORKDIR /var/www/html

# Copy application files with proper ownership (9999 is www-data in serversideup)
COPY --chown=9999:9999 . /var/www/html

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder --chown=9999:9999 /app/public/build /var/www/html/public/build

# Switch to non-root user
USER 9999

# Install optimized production dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
