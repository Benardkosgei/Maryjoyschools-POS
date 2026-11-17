<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class ExportStudents extends Command
{
    protected $signature = 'students:export {--format=json}';
    protected $description = 'Export student data for migration to production';

    public function handle()
    {
        $students = Client::whereNotNull('student_id')->get();
        
        if ($students->isEmpty()) {
            $this->error('No students found to export.');
            return 1;
        }

        $format = $this->option('format');
        $filename = 'students_export_' . date('Y-m-d_H-i-s');
        
        if ($format === 'json') {
            $data = $students->toArray();
            $content = json_encode($data, JSON_PRETTY_PRINT);
            $filename .= '.json';
        } else {
            // CSV format
            $filename .= '.csv';
            $handle = fopen('php://temp', 'r+');
            
            // Headers
            $headers = [
                'id', 'name', 'code', 'student_id', 'admission_number', 'class_grade', 
                'stream', 'house', 'date_of_birth', 'gender', 'parent_guardian_name',
                'parent_guardian_phone', 'parent_guardian_email', 'emergency_contact',
                'account_balance', 'credit_limit', 'status', 'notes', 'phone', 'email',
                'adresse', 'city', 'country', 'tax_number'
            ];
            fputcsv($handle, $headers);
            
            foreach ($students as $student) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $student->$header;
                }
                fputcsv($handle, $row);
            }
            
            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);
        }
        
        Storage::disk('local')->put($filename, $content);
        $path = storage_path('app/' . $filename);
        
        $this->info("Exported {$students->count()} students to: {$path}");
        return 0;
    }
}