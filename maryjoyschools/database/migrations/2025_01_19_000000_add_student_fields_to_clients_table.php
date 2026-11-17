<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentFieldsToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            // Student-specific fields for Kenyan school shop
            $table->string('student_id', 50)->after('code')->nullable()->unique(); // School student ID
            $table->string('admission_number', 50)->after('student_id')->nullable()->unique(); // Admission number
            $table->string('class_grade', 50)->after('admission_number')->nullable(); // Class/Grade (e.g., Form 1, Form 2, etc.)
            $table->string('stream', 20)->after('class_grade')->nullable(); // Class stream (A, B, C, etc.)
            $table->string('house', 50)->after('stream')->nullable(); // School house system
            $table->date('date_of_birth')->after('house')->nullable(); // Date of birth
            $table->enum('gender', ['Male', 'Female'])->after('date_of_birth')->nullable(); // Gender
            $table->string('parent_guardian_name', 191)->after('gender')->nullable(); // Parent/Guardian name
            $table->string('parent_guardian_phone', 20)->after('parent_guardian_name')->nullable(); // Parent/Guardian phone
            $table->string('parent_guardian_email', 191)->after('parent_guardian_phone')->nullable(); // Parent/Guardian email
            $table->text('emergency_contact')->after('parent_guardian_email')->nullable(); // Emergency contact details
            $table->decimal('account_balance', 10, 2)->after('emergency_contact')->default(0.00); // Student account balance
            $table->decimal('credit_limit', 10, 2)->after('account_balance')->default(0.00); // Credit limit for purchases
            $table->enum('status', ['Active', 'Inactive', 'Graduated', 'Transferred'])->after('credit_limit')->default('Active'); // Student status
            $table->text('notes')->after('status')->nullable(); // Additional notes about the student
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'student_id',
                'admission_number', 
                'class_grade',
                'stream',
                'house',
                'date_of_birth',
                'gender',
                'parent_guardian_name',
                'parent_guardian_phone',
                'parent_guardian_email',
                'emergency_contact',
                'account_balance',
                'credit_limit',
                'status',
                'notes'
            ]);
        });
    }
}