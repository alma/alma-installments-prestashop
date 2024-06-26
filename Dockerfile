ARG COMPOSER_VERSION=2
ARG PLATFORM_VERSION=8.1.6

FROM composer:${COMPOSER_VERSION} AS composer
FROM prestashop/prestashop:${PLATFORM_VERSION}

ENV PS_ENABLE_SSL=1

# Change root password
RUN echo 'root:alma' | chpasswd

RUN pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html/modules/alma/

RUN head -n -1 /tmp/docker_run.sh > /tmp/docker_install.sh
COPY ./scripts/entrypoint.sh /entrypoint.sh

# Composer install
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY alma/composer.json .

RUN composer install

ENTRYPOINT ["bash", "/entrypoint.sh"]
