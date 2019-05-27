#!/usr/bin/env bash

DIR=`pwd`

rm -rf ./dist/
rm -rf /tmp/alma-build/alma
mkdir -p /tmp/alma-build/alma

cp -r ./alma/* /tmp/alma-build/alma/

mkdir ./dist

cd /tmp/alma-build/
zip -9 -r "$DIR/dist/alma.zip" alma --exclude \*dist\* \*.git\* \*.idea\* \*.DS_Store

rm -rf /tmp/alma-build
