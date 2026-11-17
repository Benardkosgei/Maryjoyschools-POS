<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Services\StudentService;

class CreateTestStudents extends Command
{
    protected $signature = 'students:create-test {--count=5}';
    protected $description = 'Create test students for verification';

    public function handle()
    {
        $count = $this->option('count');
        $studentService = app(StudentService::class);
        
        $testStudents = [
            ['name' => 'John Doe', 'class_grade' => 'Form 1', 'stream' => 'A'],
            ['name' => 'Jane Smith', 'class_grade' => 'Form 2', 'stream' => 'B'],
            ['name' => 'Peter Kamau', 'class_grade' => 'Form 3', 'stream' => 'A'],
            ['name' => 'Mary Wanjiku', 'class_grade' => 'Form 4', 'stream' => 'C'],
            ['name' => 'David Ochieng', 'class_grade' => 'Grade 8', 'stream' => 'A'],
        ];
        
        $created = 0;
        
        for ($i = 0; $i < min($count, count($testStudents)); $i++) {
            $data = $testStudents[$i];
            
            $client = Client::create([
                'name' => $data['name'],
                'code' => Client::max('code') + 1,
                'student_id' => $studentService->generateStudentId(),
                'admission_number' => $studentService->generateAdmissionNumber(),
                'class_grade' => $data['class_grade'],
                'stream' => $data['stream'],
                'house' => 'Kenyatta House',
                'date_of_birth' => now()->subYears(rand(14, 18)),
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'parent_guardian_name' => 'Parent of ' . $data['name'],
                'parent_guardian_phone' => '+254' . rand(700000000, 799999999),
                'account_balance' => rand(0, 1000),
                'credit_limit' => 5000,
                'status' => 'Active'
            ]);
            
            $created++;
            $this->info("Created student: {$client->name} ({$client->student_id})");
        }
        
        $this->info("Created {$created} test students");
        return 0;
    }
}