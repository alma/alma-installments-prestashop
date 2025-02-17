#!/bin/bash
docker run --rm -v "$(pwd)/alma:/app/alma" \
  lint:ps fix --config=alma/.php-cs-fixer.dist.php -v --using-cache=yes /app
EXIT_CODE=$?

if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Fix the errors before commit!"
    exit 1
fi