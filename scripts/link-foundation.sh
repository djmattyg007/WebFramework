#!/bin/bash

ROOT="`dirname $0`/.."
ROOT=`readlink -m $ROOT`
echo $ROOT
cd $ROOT

\mkdir -p "vendor"
cd "vendor"
if [[ -d "foundation" ]]; then
    cd "foundation"
    git pull
    cd ..
else
    git clone https://github.com/djmattyg007/foundation.git
fi
cd ..

ln -s ../../../../vendor/foundation/js/foundation src/public/skin/js/foundation
ln -s ../../../../vendor/foundation/sass/normalize.scss src/public/skin/sass/normalize.scss
ln -s ../../../../vendor/foundation/sass/foundation.scss src/public/skin/sass/foundation.scss
ln -s ../../../../vendor/foundation/sass/foundation src/public/skin/sass/foundation

