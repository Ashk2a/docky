FROM php:8.2-cli-alpine

LABEL maintainer="Adrien DELHOM <adrien.delhom@outlook.com>"

ENV TZ=UTC

# Default system packets
RUN apk add --no-cache --update --repository http://nl.alpinelinux.org/alpine/edge/testing/ $PHPIZE_DEPS \
    make \
    curl \
    zip \
    unzip \
    docker-cli \
    docker-cli-compose

# Php extensions manager
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN set -eux;
RUN chmod +x /usr/local/bin/install-php-extensions

# Install PHP extensions
RUN install-php-extensions \
    bcmath \
    zip \
    @composer

# Composer configuration
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /docky
COPY . /docky
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist --no-scripts --no-dev --optimize-autoloader
RUN chmod +x /docky/docky

RUN chmod +x /docky/docker-entrypoint.sh

WORKDIR /var/app
VOLUME /var/app

ENTRYPOINT ["/docky/docker-entrypoint.sh"]
