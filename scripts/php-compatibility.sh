#!/bin/bash
php alma/vendor/bin/phpcs -p alma/ --standard=PHPCompatibility -s --runtime-set testVersion 5.6-8.1 --ignore=\*/vendor/\*
if [ $? != 0 ]
then
  echo "Fix Compatibilities errors before commit!"
  exit 1
fi