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

ARG PBF_VERSION=latest
RUN set -eux \
	&& cd PHP_CodeSniffer \
	&& if [ "${PBF_VERSION}" = "latest" ]; then \
		VERSION="$( git describe --abbrev=0 --tags )"; \
	else \
		VERSION="$( git tag | grep -E "^v?${PBF_VERSION}\.[.0-9]+\$" | sort -V | tail -1 )"; \
	fi \
	&& echo "Version: ${VERSION}" \
	&& curl -sS -L https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${VERSION}/phpcbf.phar -o /phpcbf.phar \
	&& chmod +x /phpcbf.phar \
	&& mv /phpcbf.phar /usr/bin/phpcbf \
	\
	&& phpcbf --version


ARG PHP_IMG_TAG=5.6-alpine
FROM php:${PHP_IMG_TAG} AS production

COPY --from=builder /usr/bin/phpcbf /usr/bin/phpcbf

WORKDIR /app

RUN apk add --no-cache composer
RUN composer self-update
RUN composer init -n --name="alma/phpcbf" --description="phpcbf" --type="library"
RUN composer config --no-interaction --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
RUN composer require phpcsstandards/phpcsutils --no-interaction
RUN composer require phpcsstandards/phpcsextra --no-interaction
RUN composer require escapestudios/symfony2-coding-standard --no-interaction
RUN composer require squizlabs/php_codesniffer --no-interaction
RUN composer require phpcompatibility/php-compatibility --no-interaction
RUN composer require phpcompatibility/phpcompatibility-paragonie:"*"

RUN phpcbf --config-set installed_paths /app/vendor/phpcsstandards/phpcsutils,/app/vendor/phpcsstandards/phpcsextra,/app/vendor/escapestudios/symfony2-coding-standard,/app/vendor/squizlabs/php_codesniffer,/app/vendor/phpcompatibility/php-compatibility,/app/vendor/phpcompatibility/phpcompatibility-paragonie

COPY phpcs.xml /app/phpcs.xml

ENTRYPOINT ["phpcbf"]
CMD ["--version"]