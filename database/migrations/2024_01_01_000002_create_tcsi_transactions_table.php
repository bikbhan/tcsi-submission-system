<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50)->unique();
            $table->enum('transaction_type', ['EXPORT', 'SUBMISSION']);
            $table->enum('submission_method', ['PRODA', 'API']);
            
            $table->string('provider_code', 10);
            $table->string('reporting_period', 20);
            $table->string('collection_type', 50)->default('PIR');
            
            $table->dateTime('transaction_date');
            $table->json('file_types');
            
            $table->enum('status', [
                'VALIDATING', 'VALIDATION_FAILED', 'READY_FOR_EXPORT', 'EXPORTED',
                'UPLOADED_TO_PRODA', 'SUBMITTING', 'SUBMITTED', 'PROCESSING',
                'ACCEPTED', 'ACCEPTED_WITH_WARNINGS', 'REJECTED', 'FAILED'
            ])->default('VALIDATING');
            
            $table->integer('provider_record_count')->default(0);
            $table->integer('course_record_count')->default(0);
            $table->integer('unit_record_count')->default(0);
            $table->integer('staff_record_count')->default(0);
            $table->integer('student_record_count')->default(0);
            $table->integer('unit_attempt_record_count')->default(0);
            
            $table->integer('pre_validation_error_count')->default(0);
            $table->integer('pre_validation_warning_count')->default(0);
            $table->integer('tcsi_error_count')->default(0);
            $table->integer('tcsi_warning_count')->default(0);
            
            $table->string('export_zip_filename')->nullable();
            $table->string('export_zip_path', 500)->nullable();
            $table->integer('export_zip_size')->nullable();
            
            $table->dateTime('error_report_imported_at')->nullable();
            
            $table->string('api_submission_id', 100)->nullable();
            $table->integer('api_response_code')->nullable();
            $table->text('api_response_body')->nullable();
            $table->integer('api_retry_count')->default(0);
            
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('submission_method');
            $table->index('status');
            $table->index('reporting_period');
            $table->index('transaction_date');
            $table->index(['provider_code', 'reporting_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_transactions');
    }
};
