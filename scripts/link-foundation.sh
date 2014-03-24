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

if [[ ! -h "src/public/assets/js/foundation" ]]; then
    ln -s ../../../../vendor/foundation/js/foundation src/public/assets/js/foundation
fi
if [[ ! -h "src/public/assets/js/jquery-2.1.0.min.js" ]]; then
    ln -s ../../../../vendor/foundation/js/jquery-2.1.0.min.js src/public/assets/js/jquery-2.1.0.min.js
fi
if [[ ! -h "src/public/assets/sass/normalize.scss" ]]; then
    ln -s ../../../../vendor/foundation/sass/normalize.scss src/public/assets/sass/normalize.scss
fi
if [[ ! -h "src/public/assets/sass/foundation.scss" ]]; then
    ln -s ../../../../vendor/foundation/sass/foundation.scss src/public/assets/sass/foundation.scss
fi
if [[ ! -h "src/public/assets/sass/foundation" ]]; then
    ln -s ../../../../vendor/foundation/sass/foundation src/public/assets/sass/foundation
fi

