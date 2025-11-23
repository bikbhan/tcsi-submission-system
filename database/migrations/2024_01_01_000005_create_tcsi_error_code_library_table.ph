<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_error_code_library', function (Blueprint $table) {
            $table->string('error_code', 50)->primary();
            $table->enum('file_type', ['PROVIDER', 'COURSE', 'UNIT', 'STAFF', 'STUDENT', 'UNIT_ATTEMPT']);
            $table->enum('category', ['MANDATORY', 'FORMAT', 'BUSINESS_RULE', 'REFERENCE_DATA', 'SYSTEM']);
            $table->string('field_name', 100)->nullable();
            
            $table->text('description');
            $table->text('resolution_guidance');
            
            $table->boolean('is_auto_fixable')->default(false);
            $table->string('auto_fix_function', 100)->nullable();
            $table->text('auto_fix_sql')->nullable();
            
            $table->string('help_url', 500)->nullable();
            $table->string('example_correct_value', 500)->nullable();
            
            $table->enum('severity_default', ['ERROR', 'WARNING'])->default('ERROR');
            
            $table->timestamps();
            
            $table->index('file_type');
            $table->index('category');
            $table->index('is_auto_fixable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_error_code_library');
    }
};
