#!/bin/bash -xe

# Install Symfony application dependencies
pushd application
composer update --no-interaction --no-ansi --no-progress --no-suggest --optimize-autoloader --prefer-stable
popd

# Install global project dependencies (these are primarily packages required by Travis)
composer update --no-interaction --no-ansi --no-progress --no-suggest --optimize-autoloader --prefer-stable

cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/prepend.php .
cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/append.php .
cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/phpunit_coverage.php .
cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/ExitHandler.php .
phpenv config-add travis/coverage.ini
DIR=$(realpath $(dirname "$0"))
USER=$(whoami)
PHP_VERSION=$(phpenv version-name)
ROOT=$(realpath "$DIR/..")
PORT=9000
SERVER="/tmp/php.sock"
PHP_FPM_BIN="$HOME/.phpenv/versions/$PHP_VERSION/sbin/php-fpm"
function tmpl {
    sed \
        -e "s|{DIR}|$DIR|g" \
        -e "s|{USER}|$USER|g" \
        -e "s|{PHP_VERSION}|$PHP_VERSION|g" \
        -e "s|{ROOT}|$ROOT|g" \
        -e "s|{PORT}|$PORT|g" \
        -e "s|{SERVER}|$SERVER|g" \
        < $1 > $2
}
tmpl travis/php-fpm.tmpl.conf travis/php-fpm.conf
tmpl travis/nginx.tmpl.conf travis/nginx.conf
$PHP_FPM_BIN --fpm-config travis/php-fpm.conf
nginx -c $PWD/travis/nginx.conf &
