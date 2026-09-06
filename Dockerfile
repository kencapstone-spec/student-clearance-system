FROM serversideup/php:8.3-fpm-nginx

# Copy Node.js 22 and npm from official Debian-based Node image
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /var/www/html

# Copy application files (with 9999:9999 ownership for www-data)
COPY --chown=9999:9999 . /var/www/html

# Provide a temporary .env template for build-time tools (like wayfinder)
RUN cp -n .env.example .env

# Switch to non-root user
USER 9999

# 1. Install production PHP dependencies (generates vendor/autoload.php)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 2. Install Node dependencies, build Vite assets, and prune node_modules
RUN npm ci && npm run build && rm -rf node_modules

# Production environment config
ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_FORCE=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true \
    SSL_MODE=off
