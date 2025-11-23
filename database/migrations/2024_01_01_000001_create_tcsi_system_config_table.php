<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcsi_system_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 100)->unique();
            $table->text('config_value')->nullable();
            $table->enum('config_type', ['STRING', 'INTEGER', 'BOOLEAN', 'JSON'])->default('STRING');
            $table->text('description')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            
            $table->index('config_key');
            $table->index('is_editable');
        });
        
        // Insert default configuration
        $configs = [
            ['config_key' => 'tcsi_submission_method', 'config_value' => 'proda', 'config_type' => 'STRING', 'description' => 'Current submission method: proda or api', 'is_editable' => true],
            ['config_key' => 'tcsi_api_enabled', 'config_value' => 'false', 'config_type' => 'BOOLEAN', 'description' => 'Enable API direct submission', 'is_editable' => true],
            ['config_key' => 'tcsi_api_endpoint', 'config_value' => 'https://tcsi.edu.au/api/v2/', 'config_type' => 'STRING', 'description' => 'TCSI API base URL', 'is_editable' => true],
            ['config_key' => 'tcsi_api_key', 'config_value' => '', 'config_type' => 'STRING', 'description' => 'TCSI API authentication key', 'is_editable' => true],
            ['config_key' => 'tcsi_api_timeout', 'config_value' => '300', 'config_type' => 'INTEGER', 'description' => 'API timeout in seconds', 'is_editable' => true],
            ['config_key' => 'tcsi_proda_enabled', 'config_value' => 'true', 'config_type' => 'BOOLEAN', 'description' => 'Enable PRODA export method', 'is_editable' => true],
            ['config_key' => 'tcsi_auto_validation', 'config_value' => 'true', 'config_type' => 'BOOLEAN', 'description' => 'Auto-validate before submission', 'is_editable' => true],
            ['config_key' => 'tcsi_block_on_errors', 'config_value' => 'true', 'config_type' => 'BOOLEAN', 'description' => 'Block submission if errors exist', 'is_editable' => true],
            ['config_key' => 'tcsi_provider_code', 'config_value' => 'PRV12345', 'config_type' => 'STRING', 'description' => 'TCSI Provider Code', 'is_editable' => false],
        ];
        
        foreach ($configs as $config) {
            $config['created_at'] = now();
            $config['updated_at'] = now();
            DB::table('tcsi_system_config')->insert($config);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tcsi_system_config');
    }
};
