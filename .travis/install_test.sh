#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

# Coveralls client install
wget https://github.com/satooshi/php-coveralls/releases/download/v1.0.1/coveralls.phar --output-document="${HOME}/bin/coveralls"
chmod u+x "${HOME}/bin/coveralls"

composer update --prefer-dist --no-interaction --prefer-stable ${COMPOSER_FLAGS}
