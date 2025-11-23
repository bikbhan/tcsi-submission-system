<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50);
            
            $table->enum('item_type', ['PROVIDER', 'COURSE', 'UNIT', 'STAFF', 'STUDENT', 'UNIT_ATTEMPT']);
            $table->unsignedBigInteger('item_id');
            $table->string('item_identifier', 100)->nullable();
            
            $table->enum('pre_validation_status', ['PENDING', 'PASSED', 'FAILED'])->default('PENDING');
            $table->integer('pre_validation_error_count')->default(0);
            
            $table->enum('tcsi_status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->integer('tcsi_error_count')->default(0);
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('transaction_id')->references('transaction_id')->on('tcsi_transactions')->onDelete('cascade');
            
            $table->index(['transaction_id', 'item_type']);
            $table->index(['item_type', 'item_id']);
            $table->index('pre_validation_status');
            $table->index('tcsi_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_transaction_items');
    }
};
