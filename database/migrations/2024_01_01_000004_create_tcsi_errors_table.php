<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_errors', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50);
            
            $table->enum('error_source', ['PRE_VALIDATION', 'TCSI']);
            $table->enum('file_type', ['PROVIDER', 'COURSE', 'UNIT', 'STAFF', 'STUDENT', 'UNIT_ATTEMPT']);
            
            $table->string('error_code', 50);
            $table->enum('severity', ['ERROR', 'WARNING']);
            $table->string('field_name', 100)->nullable();
            $table->string('record_identifier', 100)->nullable();
            
            $table->enum('item_type', ['PROVIDER', 'COURSE', 'UNIT', 'STAFF', 'STUDENT', 'UNIT_ATTEMPT'])->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            
            $table->text('error_message');
            $table->string('error_location', 200)->nullable();
            $table->text('submitted_value')->nullable();
            $table->string('expected_format', 200)->nullable();
            
            $table->enum('resolution_status', ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'IGNORED', 'CANNOT_FIX'])->default('PENDING');
            $table->text('resolution_notes')->nullable();
            $table->text('resolution_action')->nullable();
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->dateTime('resolved_at')->nullable();
            
            $table->boolean('is_auto_fixable')->default(false);
            $table->boolean('auto_fix_attempted')->default(false);
            $table->boolean('auto_fix_success')->default(false);
            
            $table->timestamps();
            
            $table->foreign('transaction_id')->references('transaction_id')->on('tcsi_transactions')->onDelete('cascade');
            
            $table->index(['transaction_id', 'error_source', 'file_type']);
            $table->index('error_code');
            $table->index('severity');
            $table->index('resolution_status');
            $table->index(['item_type', 'item_id', 'resolution_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_errors');
    }
};
