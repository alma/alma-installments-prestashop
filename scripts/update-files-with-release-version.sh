#!/usr/bin/env bash

###############################################################################
# README
###############################################################################
# Description :
#     Script to be used in the release process to update the version
#     number in the files
# Usage :
#     ./scripts/update-files-with-release-version.sh <version>
#     (Example: ./scripts/update-files-with-release-version.sh 1.2.3)
###############################################################################

if [ $# -lt 1 ]; then
  echo 1>&2 "$0: Missing argument. Please specify a version number."
  exit 2
fi

####################
# Sanitize version (remove the 'v' prefix if present)
####################
version=`echo ${1#v}`


####################
# Update file alma/alma.php
####################
filepath="alma/alma.php"
# Update const VERSION
sed -i -E "s/VERSION = '[0-9\.]+';/VERSION = '$version'/g" $filepath
# Update $this->version
sed -i -E "s/$this->version = '[0-9\.]+';/$this->version = '$version';/g" $filepath
