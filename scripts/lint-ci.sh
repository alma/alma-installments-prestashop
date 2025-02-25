#!/bin/bash

docker run --rm -v "$(pwd)/alma:/app/alma" \
  lint:ps fix --config=alma/.php-cs-fixer.dist.php --dry-run --diff -v --using-cache=no --allow-risky=yes /app
EXIT_CODE=$?

if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Fix the errors before merging!"
    exit 1
fi
