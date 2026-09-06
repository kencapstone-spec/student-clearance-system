FROM serversideup/php:8.3-fpm-nginx

# Switch to root to install Node.js and symlinks in /usr/local/bin
USER root

COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /var/www/html

# Copy application files with proper ownership
COPY --chown=9999:9999 . /var/www/html

# Provide a temporary .env template for build-time tools (like wayfinder)
RUN cp -n .env.example .env && chown 9999:9999 .env

# Switch back to non-root application user
USER 9999

# Configure npm cache in /tmp to prevent any permission issues
ENV npm_config_cache=/tmp/.npm

# 1. Install production PHP dependencies (generates vendor/autoload.php for wayfinder)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 2. Install Node dependencies, build Vite assets, and prune node_modules
RUN npm ci && npm run build && rm -rf node_modules

# ServerSideUp production environment configuration
ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_FORCE=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true \
    SSL_MODE=off
