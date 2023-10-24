#!/usr/bin/env bash

# Build alma.zip from the module's source directory
# Should be run from the project's source

DIR=$(pwd)

echo $DIR
mkdir -p ./tmp/build/alma
cp CHANGELOG.md ./alma/
cp -r CHANGELOG.md ./alma/* ./tmp/build/alma
rm -f ./tmp/build/alma/composer.lock
rm -rf ./tmp/build/alma/tests

if [ ! -d "./dist" ]
then
  mkdir ./dist
fi

cd ./tmp/build/alma || exit

rm -rf vendor config.xml config_*.xml
wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php
mv composer.phar /usr/local/bin/composer
php /usr/local/bin/composer install --no-dev --optimize-autoloader
php /usr/local/bin/composer dump-autoload --optimize

"$DIR/alma/vendor/bin/autoindex" prestashop:add:index

#cd ..

zip -9 -r "$DIR/dist/alma.zip" ./alma --exclude  "*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf "$DIR/tmp/build"
