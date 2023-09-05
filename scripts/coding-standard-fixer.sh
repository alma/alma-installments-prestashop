#!/bin/bash
php alma/vendor/bin/php-cs-fixer fix alma
if [ $? != 0 ]
then
  echo "Fix the errors with PHPcbf automatic fixer before commit!"
  exit 1
fi
