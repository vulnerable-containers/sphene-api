FROM php:8.2.35-fpm-bookworm

RUN apt update && apt install -y \
    git \
    curl \
    libzip-dev \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer