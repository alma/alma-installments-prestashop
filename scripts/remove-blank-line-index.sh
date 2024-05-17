#!/usr/bin/env bash

find alma -name "index.php"|while read file; do
    echo "$file"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' -e '/^\s*$/d' "$file"
    else
        sed -i -e '/^\s*$/d' "$file"
    fi
done

# sed -i '' -e '/^\s*$/d' alma/includes/index.php