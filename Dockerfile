FROM serversideup/php:8.3-fpm-nginx

USER root

# Copy Node.js 22 and npm from official Debian-based Node image
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Provide a temporary .env template for build-time tools (like wayfinder)
RUN cp -n .env.example .env

# Set Composer environment
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# 1. Install production PHP dependencies
RUN composer install --no-dev --prefer-dist -o

# 2. Install Node dependencies, build Vite assets, and prune node_modules
RUN npm ci && npm run build && rm -rf node_modules

# Ensure proper ownership for application files
RUN chown -R 9999:9999 /var/www/html

# ServerSideUp production environment configuration
# Note: Do NOT set USER 9999 here; ServerSideUp's entrypoint script requires
# initial root to generate /etc/nginx/nginx.conf then automatically drops privileges to 9999.
ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_FORCE=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true \
    SSL_MODE=off
