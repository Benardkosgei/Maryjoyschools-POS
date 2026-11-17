<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToStudentFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Add indexes for frequently queried student fields
            $table->index('student_id', 'idx_clients_student_id');
            $table->index('admission_number', 'idx_clients_admission_number');
            $table->index('class_grade', 'idx_clients_class_grade');
            $table->index('status', 'idx_clients_status');
            $table->index(['class_grade', 'stream'], 'idx_clients_class_stream');
            $table->index(['status', 'class_grade'], 'idx_clients_status_class');
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
            $table->dropIndex('idx_clients_student_id');
            $table->dropIndex('idx_clients_admission_number');
            $table->dropIndex('idx_clients_class_grade');
            $table->dropIndex('idx_clients_status');
            $table->dropIndex('idx_clients_class_stream');
            $table->dropIndex('idx_clients_status_class');
        });
    }
}