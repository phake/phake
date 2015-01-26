#!/usr/bin/env bash
if [ "$TRAVIS_PHP_VERSION" -ne "5.2" ]; then

    # Install the nightly build of HHVM ...
    composer install --dev
fi
