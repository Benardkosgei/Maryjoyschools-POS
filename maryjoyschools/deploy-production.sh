#!/bin/bash

echo "=== Mary Joy Schools Inventory - Production Deployment ==="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

echo "1. Checking current migration status..."
php artisan migrate:status

echo "2. Running database migrations..."
php artisan migrate --force

echo "3. Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "4. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "5. Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "Note: Could not change ownership (may need sudo)"

echo "6. Running student module diagnostic..."
php artisan students:diagnose

echo "7. Checking if students exist..."
php artisan tinker --execute="echo 'Students in database: ' . App\Models\Client::whereNotNull('student_id')->count();"

echo "=== Deployment Complete ==="
echo "Visit: https://inventory.maryjoyschools.com/app/People/Customers"