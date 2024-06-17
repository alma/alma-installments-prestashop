FROM composer:2.7 AS composer
FROM php:8.3-cli-alpine AS php

# Prepare Packages requirements
RUN apk update && apk add zip

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENTRYPOINT []
