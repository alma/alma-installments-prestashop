#!/usr/bin/env bash

# Build alma.zip from the module's source directory
# Should be run from the project's source

DIR=$(pwd)

mkdir -p ./tmp/build/alma
cp -r ./alma/* ./tmp/build/alma

mkdir ./dist

cd ./tmp/build/alma || exit

rm -rf vendor config.xml config_fr.xml
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize

"$DIR/tools/vendor/bin/autoindex" prestashop:add:index

cd ..

zip -9 -r "$DIR/dist/alma.zip" alma --exclude  "*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf "$DIR/tmp/build"
