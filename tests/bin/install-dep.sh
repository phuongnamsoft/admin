#!/usr/bin/env bash

cp -f ./tests/resources/stubs/artisan ./laravel-tests/
cp -f ./tests/resources/stubs/ComposerConfigCommand.php ./laravel-tests/app/
mkdir ./laravel-tests/pns-admin
cp -rf ./config ./laravel-tests/pns-admin
cp -rf ./database ./laravel-tests/pns-admin
cp -rf ./resources ./laravel-tests/pns-admin
cp -rf ./src ./laravel-tests/pns-admin
cp -rf ./tests ./laravel-tests/pns-admin
cp -rf ./composer.json ./laravel-tests/pns-admin
rm -rf ./laravel-tests/tests
cp -rf ./tests ./laravel-tests/tests
cp -f ./phpunit.dusk.xml ./laravel-tests
cp -f ./.env.testing ./laravel-tests/.env
cd ./laravel-tests
php artisan admin:composer-config
composer require pns/laravel-admin:*@dev
composer require "laravel/dusk:*" --dev # --ignore-platform-reqs
