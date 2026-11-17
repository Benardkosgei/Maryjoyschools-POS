<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = config('student.validation_rules');
        
        // Modify unique rules for updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $studentId = $this->route('client') ?? $this->route('id');
            
            if (isset($rules['student_id'])) {
                $rules['student_id'] = str_replace('unique:clients,student_id', 
                    'unique:clients,student_id,' . $studentId, $rules['student_id']);
            }
            
            if (isset($rules['admission_number'])) {
                $rules['admission_number'] = str_replace('unique:clients,admission_number', 
                    'unique:clients,admission_number,' . $studentId, $rules['admission_number']);
            }
        }
        
        return array_merge([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'adresse' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:191',
        ], $rules);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'student_id.required' => 'Student ID is required.',
            'student_id.unique' => 'This Student ID is already taken.',
            'admission_number.required' => 'Admission number is required.',
            'admission_number.unique' => 'This admission number is already taken.',
            'class_grade.required' => 'Class/Grade is required.',
            'class_grade.in' => 'Invalid class/grade selected.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'gender.required' => 'Gender is required.',
            'parent_guardian_name.required' => 'Parent/Guardian name is required.',
            'parent_guardian_phone.required' => 'Parent/Guardian phone is required.',
            'parent_guardian_email.email' => 'Parent/Guardian email must be valid.',
            'account_balance.numeric' => 'Account balance must be a number.',
            'account_balance.min' => 'Account balance cannot be negative.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
        ];
    }
}