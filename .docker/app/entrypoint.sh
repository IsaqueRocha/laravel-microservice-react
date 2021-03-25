#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh

### FRONT-END
npm config set cache /var/www/.npm-cache --global
cd frontend && npm install && cd ..

### BACK-END
pwd
cd backend
chown -R www-data:www-data .

if [ ! -f ".env" ]; then
    cp .env.exmaple .env
fi

composer install
php artisan key:generate
php artisan migrate

php-fpm
