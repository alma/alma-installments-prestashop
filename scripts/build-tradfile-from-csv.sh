#!/usr/bin/env bash

# Build file trad from the csv file directory
# Should be run from the project's source

##README :
## Install md5sum if it not found : $ brew install md5sha1sum
## execute file in bash and not sh for good MD5 (bash build-tradfile-from-csv.sh ./myfile.csv)
## Check separator is well ";" not ","
## If you have problème carraige return. Check is the file to convert is well Unix (LF) ans not DOS/Windows (CRLF)
## If the last trad is not generated, add carriage return after le last ligne of trad

INPUT=$1

OLDIFS=$IFS
IFS=';'
NBCOLUMN=$(head -1 $INPUT | sed 's/[^${IFS}]//g' | wc -c | sed 's/^ *//g')

(( $NBCOLUMN < 3 )) && { echo "$INPUT file have not 3 column minimum"; exit 99; }
[ ! -f $INPUT ] && { echo "$INPUT file not found"; exit 99; }

read -r -d '' BEGIN <<- EOF
<?php

/**
 * 2018-2022 Alma SAS
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
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

global \$_MODULE;
\$_MODULE = [];
EOF

line=1
while read hook en fr es nl de it
do
    hook=$(sed 's/\"//g' <<< $hook)
    [[ -z "$hook" ]] && echo "empty hook at line:$line"
    [[ -z "$en" ]] && echo "empty en translation for hook '$hook' at line:$line"
    [[ -z "$fr" ]] && echo "empty fr translation for en '$en' at line:$line"
    [[ -z "$es" ]] && echo "empty es translation for fr '$fr' at line:$line"
    [[ -z "$nl" ]] && echo "empty nl translation for es '$es' at line:$line"
    [[ -z "$de" ]] && echo "empty de translation for nl '$nl' at line:$line"
    [[ -z "$it" ]] && echo "empty it translation for de '$de' at line:$line"
    if [ x$hook != "xFile" ] && [ x$en != "xEN" ] && [ x$fr != "xFR" ] && [ x$es != "xES" ] && [ x$nl != "xNL" ] && [ x$de != "xDE" ] && [ x$it != "xIT" ]; then
        en=$(sed 's/\"//g' <<< $en)
        fr=$(sed 's/\"//g' <<< $fr)
        es=$(sed 's/\"//g' <<< $es)
        nl=$(sed 's/\"//g' <<< $nl)
        de=$(sed 's/\"//g' <<< $de)
        it=$(sed 's/\"//g' <<< $it)
        MD5="$(echo -n ${en//\'/\\\'} | md5)"
        NAMEFILE=$hook
        ALLEN="$ALLEN\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${en//\'/\\\'}';"
        ALLFR="$ALLFR\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${fr//\'/\\\'}';"
        ALLES="$ALLES\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${es//\'/\\\'}';"
        ALLNL="$ALLNL\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${nl//\'/\\\'}';"
        ALLDE="$ALLDE\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${de//\'/\\\'}';"
        ALLIT="$ALLIT\n\$_MODULE['<{alma}prestashop>${NAMEFILE}_${MD5}'] = '${it//\'/\\\'}';"
    fi
    let line++
done < $INPUT
IFS=$OLDIFS
echo -e "$BEGIN$ALLEN" > en.php
echo -e "$BEGIN$ALLFR" > fr.php
echo -e "$BEGIN$ALLES" > es.php
echo -e "$BEGIN$ALLNL" > nl.php
echo -e "$BEGIN$ALLDE" > de.php
echo -e "$BEGIN$ALLIT" > it.php