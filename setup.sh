echo ' '
echo 'APPLICATION SETUP'
echo ' '
echo 'RUNNING COMPOSER INSTALL'
echo '================================================================================'
composer install
echo ' '
echo 'COPYING ENV VARS'
echo '================================================================================'
cp .env.example .env
echo ' '
echo '  Coping environment variables from .env.example to .env'
echo ' '
echo 'GENERATING APP KEY'
echo '================================================================================'
php artisan key:generate
echo ' '
echo 'MIGRATING FRESH DATABASE'
echo '================================================================================'
touch database/database.sqlite
php artisan migrate:fresh
echo ' '
echo 'SEEDING DEMO DATA INTO DATABASE'
echo '================================================================================'
php artisan db:seed
echo ' '
echo 'CLEARING CACHE'
echo '================================================================================'
php artisan optimize:clear
echo ' '
echo 'SETUP COMPELTE'
echo '================================================================================'
echo ' '
echo 'You are all set now!!'
echo ' '