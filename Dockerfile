FROM php:7.4-cli

# Correction des dépôts Debian Bullseye
RUN echo "deb http://deb.debian.org/debian bullseye main contrib non-free" > /etc/apt/sources.list \
 && echo "deb http://deb.debian.org/debian bullseye-updates main contrib non-free" >> /etc/apt/sources.list \
 && echo "deb http://deb.debian.org/debian-security bullseye-security main contrib non-free" >> /etc/apt/sources.list

# Installer dépendances
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        curl \
        libzip-dev \
        zip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libicu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install zip gd mbstring intl opcache pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
# Installation de Composer 1 (version LTS finale)
RUN curl -L https://getcomposer.org/download/1.10.27/composer.phar \
    -o /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

WORKDIR /var/www
