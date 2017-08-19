#!/bin/bash -xe

if ! ./vendor/bin/phpunit --verbose --coverage-text tests ; then
  ls index.php*
  cat /tmp/selenium-server.log || true
  cat travis/error.log || true
  cat travis/access.log || true
  cat travis/php-error.log || true
  cat travis/php-access.log || true
  exit -1
fi
