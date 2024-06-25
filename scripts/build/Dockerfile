FROM composer:2.2 AS composer
FROM php:5.6-cli-alpine AS php

# Prepare Packages requirements
RUN apk update && apk add zip

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENTRYPOINT []
