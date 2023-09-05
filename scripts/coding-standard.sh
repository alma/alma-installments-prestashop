#!/bin/bash
php alma/vendor/bin/php-cs-fixer fix --dry-run alma
if [ $? != 0 ]
then
  echo "Fix the errors before commit!"
  exit 1
fi
