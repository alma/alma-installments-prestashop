ARG PHP_IMG_TAG=7.4-alpine
FROM php:${PHP_IMG_TAG} AS production

WORKDIR /app

RUN apk add --no-cache bash curl git libxml2-dev \
    && docker-php-ext-install simplexml tokenizer xmlwriter

RUN curl -L https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.34.0/php-cs-fixer.phar \
    -o /usr/local/bin/php-cs-fixer \
 && chmod +x /usr/local/bin/php-cs-fixer

ENTRYPOINT ["php-cs-fixer"]
CMD ["--version"]
