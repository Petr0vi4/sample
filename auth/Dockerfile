ARG PHP_VERSION=7.4.8
ARG NGINX_VERSION=1.18.0
ARG COMPOSER_VERSION=1.10.9

# ------------------------------------------------------ FPM -----------------------------------------------------------

FROM php:${PHP_VERSION}-fpm-alpine AS php-fpm-base

RUN apk add --no-cache libpq postgresql-dev $PHPIZE_DEPS && \
    docker-php-ext-install opcache pdo_pgsql && \
    pecl install apcu &&\
    docker-php-ext-enable apcu && \
    docker-php-source delete && \
    apk --purge del postgresql-dev $PHPIZE_DEPS
RUN curl -sS https://raw.githubusercontent.com/composer/getcomposer.org/7bfcc5eaf3af1fe20e172a8676d2fb9bb8162d7e/web/installer \
    | php -- --install-dir=/usr/local/bin/ --filename=composer
RUN mkdir /var/www/app && \
    chown www-data:www-data /var/www/app
COPY .docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
WORKDIR /var/www/app
EXPOSE 9000

FROM php-fpm-base AS php-fpm

ENV APP_ENV prod
ENV APP_DEBUG 0

USER www-data
COPY --chown=www-data:www-data composer.json composer.lock symfony.lock .env ./
RUN composer install --no-dev --no-ansi --no-interaction --no-progress --no-scripts --optimize-autoloader
COPY --chown=www-data:www-data migrations ./migrations
COPY --chown=www-data:www-data public ./public
COPY --chown=www-data:www-data bin/console ./bin/console
COPY --chown=www-data:www-data config ./config
COPY --chown=www-data:www-data src ./src
RUN composer run-script post-install-cmd && composer clear-cache
USER root

# ----------------------------------------------------- NGINX ----------------------------------------------------------

FROM nginx:${NGINX_VERSION}-alpine AS nginx-base

ENV NGINX_ENTRYPOINT_QUIET_LOGS 1

COPY .docker/nginx/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80

FROM nginx-base AS nginx

COPY --from=php-fpm --chown=nginx:nginx /var/www/app/public /var/www/app/public
