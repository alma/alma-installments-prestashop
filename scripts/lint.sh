#!/bin/bash
CACHE_FILE="$(pwd)/.php_cs.cache"
if [[ ! -f "$CACHE_FILE" ]]; then
    echo "{}" > "$CACHE_FILE"
fi

docker run --rm -v "$(pwd)/alma:/app/alma" -v "$(pwd)/.php_cs.cache:/app/.php_cs.cache" \
  lint:ps fix --config=alma/.php-cs-fixer.dist.php -v --dry-run --using-cache=yes --cache-file=/app/.php_cs.cache --allow-risky=yes /app
EXIT_CODE=$?

if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Fix the errors before commit!"
    exit 1
fi