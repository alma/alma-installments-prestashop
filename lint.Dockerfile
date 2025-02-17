ARG PHP_IMG_TAG=5.6-alpine
FROM php:${PHP_IMG_TAG} AS production

WORKDIR /composer

RUN apk add --no-cache composer
RUN composer self-update
RUN composer init -n --name="alma/php-cs" --description="php-cs" --type="library"
RUN composer require friendsofphp/php-cs-fixer --no-interaction
RUN composer require prestashop/php-coding-standards --no-interaction

WORKDIR /app

ENTRYPOINT ["/composer/vendor/bin/php-cs-fixer"]
CMD ["--version"]