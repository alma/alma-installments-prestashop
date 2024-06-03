#!/usr/bin/env bash

# Build alma.zip from the module's source directory

SRC_DIR=$(pwd)
BUILD_DIR="/tmp/build/alma"

mkdir -p $BUILD_DIR
cp CHANGELOG.md $SRC_DIR/alma/
cp -r $SRC_DIR/alma/* $BUILD_DIR
cp $SRC_DIR/alma/.htaccess $BUILD_DIR/

if [ ! -d "./dist" ]; then
  mkdir ./dist
fi

cd $BUILD_DIR

rm -f composer.lock config.xml config_*.xml phpunit.ci.xml phpunit.dist.xml
rm -rf tests vendor

php /usr/bin/composer install --no-dev --optimize-autoloader
php /usr/bin/composer dump-autoload --optimize
php vendor/bin/autoindex prestashop:add:index

cd ..

zip -9 -r "$SRC_DIR/dist/alma.zip" alma --exclude "*/*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf $BUILD_DIR
rm -rf "$SRC_DIR/alma/CHANGELOG.md"
