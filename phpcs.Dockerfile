ARG PHP_IMG_TAG=5.6-alpine
FROM php:${PHP_IMG_TAG} AS builder

# Install build dependencies
RUN set -eux \
	&& apk add --no-cache \
		ca-certificates \
		# coreutils add 'sort -V'
		coreutils \
		curl \
		git \
	&& git clone https://github.com/PHPCSStandards/PHP_CodeSniffer

ARG PCS_VERSION=latest
RUN set -eux \
	&& cd PHP_CodeSniffer \
	&& if [ "${PCS_VERSION}" = "latest" ]; then \
		VERSION="$( git describe --abbrev=0 --tags )"; \
	else \
		VERSION="$( git tag | grep -E "^v?${PCS_VERSION}\.[.0-9]+\$" | sort -V | tail -1 )"; \
	fi \
	&& echo "Version: ${VERSION}" \
	&& curl -sS -L https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${VERSION}/phpcs.phar -o /phpcs.phar \
	&& chmod +x /phpcs.phar \
	&& mv /phpcs.phar /usr/bin/phpcs \
	\
	&& phpcs --version

ARG PHP_IMG_TAG=5.6-alpine
FROM php:${PHP_IMG_TAG} AS production

COPY --from=builder /usr/bin/phpcs /usr/bin/phpcs

WORKDIR /app

RUN apk add --no-cache composer
RUN composer self-update
RUN composer init -n --name="alma/phpcs" --description="phpcbf" --type="library"
RUN composer config --no-interaction --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
RUN composer require phpcsstandards/phpcsutils --no-interaction
RUN composer require phpcsstandards/phpcsextra --no-interaction
RUN composer require escapestudios/symfony2-coding-standard --no-interaction
RUN composer require squizlabs/php_codesniffer --no-interaction
RUN composer require phpcompatibility/php-compatibility --no-interaction
RUN composer require phpcompatibility/phpcompatibility-paragonie:"*"

RUN phpcs --config-set installed_paths /app/vendor/phpcsstandards/phpcsutils,/app/vendor/phpcsstandards/phpcsextra,/app/vendor/escapestudios/symfony2-coding-standard,/app/vendor/squizlabs/php_codesniffer,/app/vendor/phpcompatibility/php-compatibility,/app/vendor/phpcompatibility/phpcompatibility-paragonie

COPY phpcs.xml /app/phpcs.xml

ENTRYPOINT ["phpcs"]
CMD ["--version"]