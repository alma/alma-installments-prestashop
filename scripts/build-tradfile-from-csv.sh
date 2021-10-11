#!/usr/bin/env bash

# Build file trad from the csv file directory
# Should be run from the project's source

##README :
## Install md5sum if it not found : $ brew install md5sha1sum
## execute file in bash and not sh for good MD5 (bash build-tradfile-from-csv.sh ./myfile.csv)
## Check separator is well ";" not ","
## If you have problème carraige return. Check is the file to convert is well Unix (LF) ans not DOS/Windows (CRLF)

INPUT=$1

OLDIFS=$IFS
IFS=';'
NBCOLUMN=$(head -1 $INPUT | sed 's/[^;]//g' | wc -c | sed 's/^ *//g')

(( $NBCOLUMN < 3 )) && { echo "$INPUT file have not 3 column minimum"; exit 99; }
[ ! -f $INPUT ] && { echo "$INPUT file not found"; exit 99; }

read -r -d '' BEGIN <<- EOF
<?php

/**
 * 2018-2021 Alma SAS
 * 
 * THE MIT LICENSE
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

global \$_MODULE;
\$_MODULE = [];
EOF

line=1
while read hook en fr
do
    hook=$(sed 's/\"//g' <<< $hook)
    [[ -z "$hook" ]] && echo "empty hook at line:$line"
    [[ -z "$en" ]] && echo "empty en translation for hook '$hook' at line:$line"
    [[ -z "$fr" ]] && echo "empty fr translation for en '$en' at line:$line"
    if [ x$hook != "xFile" ] && [ x$en != "xEN" ] && [ x$fr != "xFR" ]; then
        en=$(sed 's/\"//g' <<< $en)
        fr=$(sed 's/\"//g' <<< $fr)
        MD5="$(echo -n $en | md5)"
        NAMEFILE=$hook
        ALLEN="$ALLEN\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}}'] = '${en//\'/\\\'}';"
        ALLFR="$ALLFR\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}}'] = '${fr//\'/\\\'}';"
    fi
    let line++
done < $INPUT
IFS=$OLDIFS
echo -e "$BEGIN$ALLEN" > en.php
echo -e "$BEGIN$ALLFR" > fr.php