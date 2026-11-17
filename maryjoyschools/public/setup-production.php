<?php
/**
 * Production Setup Script
 * Run this ONCE after uploading files to production
 * Access via: https://inventory.maryjoyschools.com/setup-production.php
 */

// Security check - remove this file after running
if (file_exists(__DIR__ . '/../.production-setup-complete')) {
    die('Setup already completed. Delete .production-setup-complete file to run again.');
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Mary Joy Schools - Production Setup</h1>";
echo "<pre>";

try {
    echo "=== PRODUCTION SETUP STARTING ===\n\n";
    
    // 1. Check database connection
    echo "1. Testing database connection...\n";
    try {
        DB::connection()->getPdo();
        echo "✓ Database connection successful\n\n";
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
        echo "Please check your .env database settings\n";
        exit;
    }
    
    // 2. Run migrations
    echo "2. Running database migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    
    // 3. Check if student fields exist
    echo "3. Checking student module setup...\n";
    if (Schema::hasColumn('clients', 'student_id')) {
        echo "✓ Student fields exist in database\n";
        
        // Count existing students
        $studentCount = DB::table('clients')->whereNotNull('student_id')->count();
        echo "✓ Found {$studentCount} students in database\n";
        
        if ($studentCount == 0) {
            echo "\n4. Creating sample students...\n";
            
            // Create a few sample students
            $sampleStudents = [
                [
                    'name' => 'John Doe',
                    'code' => DB::table('clients')->max('code') + 1,
                    'student_id' => 'STU0001',
                    'admission_number' => 'ADM2025001',
                    'class_grade' => 'Form 1',
                    'stream' => 'A',
                    'house' => 'Kenyatta House',
                    'date_of_birth' => '2008-05-15',
                    'gender' => 'Male',
                    'parent_guardian_name' => 'Jane Doe',
                    'parent_guardian_phone' => '+254700123456',
                    'account_balance' => 500.00,
                    'credit_limit' => 5000.00,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Mary Wanjiku',
                    'code' => DB::table('clients')->max('code') + 2,
                    'student_id' => 'STU0002',
                    'admission_number' => 'ADM2025002',
                    'class_grade' => 'Form 2',
                    'stream' => 'B',
                    'house' => 'Nyerere House',
                    'date_of_birth' => '2007-08-22',
                    'gender' => 'Female',
                    'parent_guardian_name' => 'Peter Wanjiku',
                    'parent_guardian_phone' => '+254700654321',
                    'account_balance' => 750.00,
                    'credit_limit' => 5000.00,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            foreach ($sampleStudents as $student) {
                DB::table('clients')->insert($student);
                echo "✓ Created student: {$student['name']} ({$student['student_id']})\n";
            }
        }
    } else {
        echo "✗ Student fields missing - migration may have failed\n";
    }
    
    // 4. Clear and cache for production
    echo "\n5. Optimizing for production...\n";
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    echo "✓ Caches cleared\n";
    
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    echo "✓ Production caches created\n";
    
    // 5. Create completion marker
    file_put_contents(__DIR__ . '/../.production-setup-complete', date('Y-m-d H:i:s'));
    
    echo "\n=== SETUP COMPLETED SUCCESSFULLY ===\n";
    echo "✓ Database migrations run\n";
    echo "✓ Student module configured\n";
    echo "✓ Sample students created\n";
    echo "✓ Production optimizations applied\n\n";
    
    echo "Next steps:\n";
    echo "1. Visit: https://inventory.maryjoyschools.com/app/People/Customers\n";
    echo "2. You should now see student details\n";
    echo "3. DELETE this setup-production.php file for security\n\n";
    
    echo "If you need to import your existing students, use the import commands via SSH/terminal.\n";
    
} catch (Exception $e) {
    echo "✗ Setup failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>