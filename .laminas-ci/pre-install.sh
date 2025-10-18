#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r .php)

if [[ "${PHP_VERSION}" != "8.5" ]]; then
    exit 0
fi

composer require --dev phpunit/phpunit:^10.5.58 --no-update --update-with-dependencies
