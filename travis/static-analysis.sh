#!/bin/bash -xe

# basic syntax check against all php files
find tests application/src -name '*.php' | xargs -n 1 php -l
./vendor/bin/phpcs -n --extensions=php --ignore=*/vendor/* --standard=PSR2 tests application/src/*
