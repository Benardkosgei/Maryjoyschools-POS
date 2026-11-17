<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportStudents extends Command
{
    protected $signature = 'students:import {file} {--format=json} {--update-existing}';
    protected $description = 'Import student data from exported file';

    public function handle()
    {
        $filename = $this->argument('file');
        $format = $this->option('format');
        $updateExisting = $this->option('update-existing');
        
        if (!Storage::disk('local')->exists($filename)) {
            $this->error("File not found: {$filename}");
            return 1;
        }
        
        $content = Storage::disk('local')->get($filename);
        
        if ($format === 'json') {
            $students = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format');
                return 1;
            }
        } else {
            // CSV format
            $lines = str_getcsv($content, "\n");
            $headers = str_getcsv(array_shift($lines));
            $students = [];
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $data = str_getcsv($line);
                $students[] = array_combine($headers, $data);
            }
        }
        
        $imported = 0;
        $updated = 0;
        $errors = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($students as $studentData) {
                // Check if student exists
                $existing = null;
                if (!empty($studentData['student_id'])) {
                    $existing = Client::where('student_id', $studentData['student_id'])->first();
                }
                
                if ($existing && $updateExisting) {
                    $existing->update($studentData);
                    $updated++;
                } elseif (!$existing) {
                    Client::create($studentData);
                    $imported++;
                } else {
                    $this->warn("Student {$studentData['student_id']} already exists, skipping...");
                }
            }
            
            DB::commit();
            $this->info("Import completed: {$imported} imported, {$updated} updated, {$errors} errors");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}