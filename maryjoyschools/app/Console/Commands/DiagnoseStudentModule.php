<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiagnoseStudentModule extends Command
{
    protected $signature = 'students:diagnose';
    protected $description = 'Diagnose student module setup and data';

    public function handle()
    {
        $this->info('=== Student Module Diagnostic ===');
        
        // Check if student fields exist
        $this->info('1. Checking database schema...');
        $studentFields = [
            'student_id', 'admission_number', 'class_grade', 'stream', 'house',
            'date_of_birth', 'gender', 'parent_guardian_name', 'parent_guardian_phone',
            'parent_guardian_email', 'emergency_contact', 'account_balance', 
            'credit_limit', 'status', 'notes'
        ];
        
        $missingFields = [];
        foreach ($studentFields as $field) {
            if (!Schema::hasColumn('clients', $field)) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            $this->info('✓ All student fields exist in clients table');
        } else {
            $this->error('✗ Missing fields: ' . implode(', ', $missingFields));
            $this->warn('Run: php artisan migrate --force');
        }
        
        // Check migration status
        $this->info('2. Checking migration status...');
        try {
            $migrations = DB::table('migrations')
                ->whereIn('migration', [
                    '2025_01_19_000000_add_student_fields_to_clients_table',
                    '2025_01_20_000000_add_indexes_to_student_fields'
                ])
                ->pluck('migration')
                ->toArray();
                
            if (count($migrations) === 2) {
                $this->info('✓ Student migrations have been run');
            } else {
                $this->error('✗ Student migrations missing: ' . (2 - count($migrations)) . ' migrations not run');
            }
        } catch (\Exception $e) {
            $this->error('✗ Cannot check migrations: ' . $e->getMessage());
        }
        
        // Check student data
        $this->info('3. Checking student data...');
        try {
            $studentCount = Client::whereNotNull('student_id')->count();
            $this->info("✓ Found {$studentCount} students in database");
            
            if ($studentCount > 0) {
                $activeStudents = Client::whereNotNull('student_id')->where('status', 'Active')->count();
                $this->info("  - Active students: {$activeStudents}");
                
                $classes = Client::whereNotNull('student_id')
                    ->distinct()
                    ->pluck('class_grade')
                    ->filter()
                    ->sort()
                    ->values();
                $this->info("  - Classes: " . $classes->implode(', '));
            }
        } catch (\Exception $e) {
            $this->error('✗ Cannot query student data: ' . $e->getMessage());
        }
        
        // Check configuration
        $this->info('4. Checking configuration...');
        if (config('student')) {
            $this->info('✓ Student configuration loaded');
            $this->info('  - Default credit limit: ' . config('student.default_credit_limit'));
            $this->info('  - Available classes: ' . count(config('student.classes')));
        } else {
            $this->error('✗ Student configuration not found');
        }
        
        // Check routes
        $this->info('5. Checking routes...');
        try {
            $routes = collect(\Route::getRoutes())->filter(function($route) {
                return str_contains($route->uri(), 'students');
            })->count();
            
            if ($routes > 0) {
                $this->info("✓ Found {$routes} student-related routes");
            } else {
                $this->warn('⚠ No student routes found - check routes/api.php');
            }
        } catch (\Exception $e) {
            $this->warn('⚠ Cannot check routes: ' . $e->getMessage());
        }
        
        $this->info('=== Diagnostic Complete ===');
        return 0;
    }
}