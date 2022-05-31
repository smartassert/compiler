ARG php_version=8.1

FROM php:${php_version}-cli-alpine

LABEL org.opencontainers.image.source="https://github.com/smartassert/compiler"

WORKDIR /app

ARG proxy_server_version=0.8
ARG php_version

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY bin/compiler /app/bin/compiler
COPY src /app/src
COPY composer.json /app/

RUN apk --no-cache add libzip-dev \
    && docker-php-ext-install pcntl zip \
    && chmod +x /app/bin/compiler \
    && ln -s /app/bin/compiler /app/compiler \
    && composer install --prefer-dist --no-dev \
    && composer check-platform-reqs --ansi \
    && rm composer.json \
    && curl -L https://raw.githubusercontent.com/webignition/tcp-cli-proxy-server/${proxy_server_version}/composer.json --output composer.json \
    && curl -L https://github.com/webignition/tcp-cli-proxy-server/releases/download/${proxy_server_version}/composer-${php_version}.lock --output composer.lock \
    && composer check-platform-reqs --ansi \
    && rm composer.json \
    && rm composer.lock \
    && rm /usr/bin/composer \
    && curl -L https://github.com/webignition/tcp-cli-proxy-server/releases/download/${proxy_server_version}/server-${php_version}.phar --output ./server \
    && chmod +x ./server

CMD ./server
