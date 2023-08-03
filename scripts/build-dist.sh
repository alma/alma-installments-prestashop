#!/usr/bin/env bash

# Build alma.zip from the module's source directory
# Should be run from the project's source

DIR=$(pwd)

mkdir -p ./tmp/build/alma
cp CHANGELOG.md ./alma/
cp -r CHANGELOG.md ./alma/* ./tmp/build/alma
rm -f ./tmp/build/alma/composer.lock

mkdir ./dist

cd ./tmp/build/alma || exit

rm -rf vendor config.xml config_*.xml
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize

"$DIR/tools/vendor/bin/autoindex" prestashop:add:index

cd ..

zip -9 -r "$DIR/dist/alma.zip" alma --exclude  "*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf "$DIR/tmp/build"
