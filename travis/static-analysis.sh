#!/bin/bash -xe

# basic syntax check against all php files
find tests index.php -name '*.php' | xargs -n 1 php -l
./vendor/bin/phpcs -n --extensions=php --ignore=*/vendor/* --standard=pacifica_php_ruleset.xml tests index.php
