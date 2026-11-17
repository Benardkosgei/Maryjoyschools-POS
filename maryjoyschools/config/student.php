<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Student Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the student management module
    |
    */

    'default_credit_limit' => env('STUDENT_DEFAULT_CREDIT_LIMIT', 5000.00),
    
    'default_account_balance' => env('STUDENT_DEFAULT_ACCOUNT_BALANCE', 0.00),
    
    'classes' => [
        'PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'
    ],
    
    'houses' => [
        'Kenyatta House',
        'Nyerere House', 
        'Mandela House',
        'Nkrumah House'
    ],
    
    'statuses' => [
        'Active',
        'Inactive', 
        'Graduated',
        'Transferred'
    ],
    
    'genders' => [
        'Male',
        'Female'
    ],
    
    'validation_rules' => [
        'student_id' => 'required|unique:clients,student_id',
        'admission_number' => 'required|unique:clients,admission_number',
        'name' => 'required|string|max:191',
        'class_grade' => 'required|in:PP1,PP2,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12,Grade 13',
        'stream' => 'nullable|string|max:50',
        'house' => 'nullable|string|max:50',
        'date_of_birth' => 'required|date|before:today',
        'gender' => 'required|in:Male,Female',
        'parent_guardian_name' => 'required|string|max:191',
        'parent_guardian_phone' => 'required|string|max:20',
        'parent_guardian_email' => 'nullable|email|max:191',
        'account_balance' => 'nullable|numeric|min:0',
        'credit_limit' => 'nullable|numeric|min:0',
        'status' => 'required|in:Active,Inactive,Graduated,Transferred'
    ],
    
    'import_csv_headers' => [
        'name',
        'studentid',
        'admissionnumber', 
        'classgrade',
        'stream',
        'house',
        'dateofbirth',
        'gender',
        'parentguardianname',
        'parentguardianphone',
        'parentguardianemail',
        'emergencycontact',
        'accountbalance',
        'creditlimit',
        'status',
        'notes',
        'phone',
        'email',
        'adresse',
        'city',
        'country'
    ],
    
    'low_balance_threshold' => env('STUDENT_LOW_BALANCE_THRESHOLD', 100.00),
    
    'negative_balance_allowed' => env('STUDENT_NEGATIVE_BALANCE_ALLOWED', false),
    
    'auto_generate_student_id' => env('STUDENT_AUTO_GENERATE_ID', true),
    
    'student_id_prefix' => env('STUDENT_ID_PREFIX', 'STU'),
    
    'admission_number_prefix' => env('STUDENT_ADMISSION_PREFIX', 'ADM'),
];