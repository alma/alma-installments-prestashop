#!/bin/bash
PROJECT_ROOT=$(git rev-parse --show-toplevel)
docker run --rm -v "$PROJECT_ROOT/alma:/app/alma" phpcs:ps --standard=PHPCompatibility -s --runtime-set testVersion 5.6-8.1 ./alma
EXIT_CODE=$?
if [[ $EXIT_CODE -ne 0 ]]; then
    echo "Check PHP code compatibility before commit!"
    exit 1
fi
