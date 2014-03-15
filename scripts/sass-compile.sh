#!/bin/bash

SASSC=`which sassc`
if [[ -z "$SASSC" ]]; then
    echo "Cannot find sassc executable. Unable to compile the sass."
    exit 1
fi

ROOT="`dirname $0`/.."
ROOT=`readlink -m $ROOT`
echo $ROOT
cd $ROOT/src/public/skin/

FILES=`find sass/ -maxdepth 1 -name '*.scss'`
echo $FILES

for file in $FILES; do
    newname="`basename $file .scss`.css"
    rm css/$newname
    $SASSC -t compressed $file > css/$newname
done

