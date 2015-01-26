#!/usr/bin/env bash

if [ "$TRAVIS_PHP_VERSION" != '5.2' ]; then
    composer install --dev
fi
