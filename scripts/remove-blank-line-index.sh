#!/usr/bin/env bash

find alma -name "index.php"|while read file; do
    echo "$file"
    sed -i '' -e '/^\s*$/d' "$file"
done

# sed -i '' -e '/^\s*$/d' alma/includes/index.php