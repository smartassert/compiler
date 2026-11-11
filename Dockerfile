ARG php_version=8.4

FROM composer:2 AS composer_build

WORKDIR /app
COPY composer.json .
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative && rm composer.lock

FROM php:${php_version}-cli-alpine

LABEL org.opencontainers.image.source="https://github.com/smartassert/compiler"

WORKDIR /app
COPY --from=composer_build /app /app

ARG proxy_server_version=1.1
ARG php_version

RUN apk --no-cache add libzip-dev \
    && docker-php-ext-install pcntl zip \
    && chmod +x /app/bin/compiler \
    && ln -s /app/bin/compiler /app/compiler \
    && rm composer.json \
    && curl -L https://github.com/webignition/tcp-cli-proxy-server/releases/download/${proxy_server_version}/server-${php_version}.phar --output ./server \
    && chmod +x ./server

CMD ./server
