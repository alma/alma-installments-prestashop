ARG PHP_IMG_TAG=7.4-alpine
FROM php:${PHP_IMG_TAG} AS production

WORKDIR /composer

RUN apk add --no-cache \
    libxml2-dev libxml2 \
    bash \
    git \
    curl \
    make \
    autoconf \
    g++ \
    unzip

RUN docker-php-ext-install xml tokenizer xmlwriter simplexml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer init -n --name="alma/php-cs" --description="php-cs" --type="library"

RUN composer config --no-interaction --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

RUN composer require squizlabs/php_codesniffer:^3.13 phpcompatibility/php-compatibility phpcompatibility/phpcompatibility-paragonie --no-interaction

RUN /composer/vendor/bin/phpcs --config-set installed_paths "/composer/vendor/escapestudios/symfony2-coding-standard,/composer/vendor/squizlabs/php_codesniffer,/composer/vendor/phpcompatibility/php-compatibility,/composer/vendor/phpcompatibility/phpcompatibility-paragonie"

WORKDIR /app

ENTRYPOINT ["/composer/vendor/bin/phpcs"]
CMD ["--version"]
