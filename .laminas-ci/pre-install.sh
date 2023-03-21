#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r .php)

if [[ "${PHP_VERSION}" != "7.2" ]] && [[ "${PHP_VERSION}" != "7.3" ]]; then
    exit 0
fi

composer require --dev phpunit/phpunit:^8.5.31 --no-update --update-with-dependencies
