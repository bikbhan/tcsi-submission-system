<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50)->nullable();
            
            $table->enum('activity_type', [
                'TRANSACTION_CREATED', 'VALIDATION_STARTED', 'VALIDATION_COMPLETED',
                'EXPORT_GENERATED', 'FILES_DOWNLOADED', 'MARKED_UPLOADED',
                'ERROR_REPORT_IMPORTED', 'SUBMISSION_STARTED', 'SUBMISSION_COMPLETED',
                'ERROR_FIXED', 'STATUS_CHANGED', 'NOTIFICATION_SENT', 'CONFIG_CHANGED'
            ]);
            
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['transaction_id', 'created_at']);
            $table->index('activity_type');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_activity_log');
    }
};
