#!/bin/bash
docker run --rm -v "$(pwd)/alma:/app/alma" -v "$(pwd)/.php_cs.cache:/app/.php_cs.cache" --entrypoint php \
  php-compatibility:ps -d memory_limit=512M /composer/vendor/bin/phpcs \
  -p alma --standard=PHPCompatibility -s --runtime-set testVersion 7.4-8.4 \
  --extensions=php \
  --ignore=\*/vendor/\*,\*/.coverage/\*
EXIT_CODE=$?

if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Check PHP code compatibility before commit!"
    exit 1
fi
