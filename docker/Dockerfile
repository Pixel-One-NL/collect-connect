FROM serversideup/php:8.5-frankenphp-alpine

WORKDIR /var/www/html

USER root

# Install NodeJS and NPM
RUN apk add --update nodejs npm

# Install PHP extensions
RUN install-php-extensions intl pcntl pdo_mysql redis sodium zip bcmath exif gd xdebug

ARG USER_ID
ARG GROUP_ID

RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID && \
    mkdir -p /composer/cache && \
    chown -R $USER_ID:$GROUP_ID /composer && \
    chmod -R u+w /composer && \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID

<<<<<<< Updated upstream
FROM base AS vendor

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
USER www-data
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM node:22-alpine AS assets

WORKDIR /var/www/html
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=vendor /var/www/html/storage ./storage
RUN npm run build

FROM base AS production

ENV PHP_OPCACHE_ENABLE=1

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

USER www-data

=======
USER www-data
>>>>>>> Stashed changes
