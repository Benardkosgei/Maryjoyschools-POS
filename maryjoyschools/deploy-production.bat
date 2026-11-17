@echo off
echo === Mary Joy Schools Inventory - Production Deployment ===

REM Check if we're in the right directory
if not exist "artisan" (
    echo Error: artisan file not found. Make sure you're in the Laravel project root.
    exit /b 1
)

echo 1. Checking current migration status...
php artisan migrate:status

echo 2. Running database migrations...
php artisan migrate --force

echo 3. Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo 4. Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo 5. Running student module diagnostic...
php artisan students:diagnose

echo 6. Checking if students exist...
php artisan tinker --execute="echo 'Students in database: ' . App\Models\Client::whereNotNull('student_id')->count();"

echo === Deployment Complete ===
echo Visit: https://inventory.maryjoyschools.com/app/People/Customers
pause