FROM php:8.1-cli-alpine

RUN apk add --no-cache bash unzip icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

USER app