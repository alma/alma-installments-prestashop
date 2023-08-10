#!/usr/bin/env bash

# Build alma.zip from the module's source directory
# Should be run from the project's source

DIR=$(pwd)

mkdir -p ./tmp/build/alma
cp CHANGELOG.md ./alma/
cp -r CHANGELOG.md ./alma/* ./tmp/build/alma
rm -f ./tmp/build/alma/composer.lock
rm -rf ./tmp/build/alma/tests

mkdir ./dist

cd ./tmp/build/alma || exit

rm -rf vendor config.xml config_*.xml
/opt/homebrew/Cellar/php@5.6/5.6.40_8/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize

"$DIR/alma/vendor/bin/autoindex" prestashop:add:index

cd ..

zip -9 -r "$DIR/dist/alma.zip" alma --exclude  "*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf "$DIR/tmp/build"
