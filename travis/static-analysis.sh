#!/bin/bash -xe

# basic syntax check against all php files
find application -name '*.php' | xargs -n 1 php -l
./vendor/bin/phpcs -n --extensions=php --ignore=*/websystem/*,*/system/*,*/migrations/*,*/libraries/*,*/logs/*,*/third_party/* --standard=pacifica_php_ruleset.xml application/
