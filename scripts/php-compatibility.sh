#!/bin/bash
docker run --rm -v "$(pwd)/alma:/app/alma" -v "$(pwd)/.php_cs.cache:/app/.php_cs.cache" --entrypoint /composer/vendor/bin/phpcs \
  lint:ps -p alma --standard=PHPCompatibility -s --runtime-set testVersion 5.6-8.1 --ignore=\*/vendor/\*,\*/.coverage/\*
EXIT_CODE=$?

if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Check PHP code compatibility before commit!"
    exit 1
fi
