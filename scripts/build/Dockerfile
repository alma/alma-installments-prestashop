# We need to keep composer in versio 2.2 to be compatible with PHP 5.6
FROM composer:2.7 AS composer
FROM php:5.6-cli-alpine AS php

# Prepare Packages requirements
RUN apk update && apk add zip

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENTRYPOINT []
