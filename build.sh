#!/usr/bin/env bash

DIR=`pwd`

rm -rf ./dist/
rm -rf /tmp/alma-build/alma-installments-prestashop
mkdir -p /tmp/alma-build/alma-installments-prestashop/alma


cp -r ./alma/* /tmp/alma-build/alma-installments-prestashop/alma

mkdir ./dist

cd /tmp/alma-build/alma-installments-prestashop/alma || exit

rm -rf vendor

composer install --no-dev

$DIR/alma/vendor/bin/autoindex prestashop:add:index

cd ..

zip -9 -r "$DIR/dist/alma.zip" alma --exclude  "*/.*" "*/build.sh" "*/dist" "*/docker*"

rm -rf /tmp/alma-build
