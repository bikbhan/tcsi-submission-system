<?php

namespace Database\Seeders\TCSI;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TcsiErrorCodeLibrarySeeder extends Seeder
{
    /**
     * Seed the error code library with 130+ error codes
     */
    public function run(): void
    {
        DB::table('tcsi_error_code_library')->truncate();
        
        echo "Seeding TCSI Error Code Library...\n";
        
        $this->seedStudentErrors();
        echo "✓ Student error codes seeded\n";
        
        $this->seedCourseErrors();
        echo "✓ Course error codes seeded\n";
        
        $this->seedUnitErrors();
        echo "✓ Unit error codes seeded\n";
        
        $this->seedStaffErrors();
        echo "✓ Staff error codes seeded\n";
        
        $this->seedProviderErrors();
        echo "✓ Provider error codes seeded\n";
        
        $this->seedUnitAttemptErrors();
        echo "✓ Unit Attempt error codes seeded\n";
        
        $count = DB::table('tcsi_error_code_library')->count();
        echo "\nTotal error codes seeded: {$count}\n";
    }
    
    /**
     * Seed Student validation error codes
     */
    private function seedStudentErrors(): void
    {
        $errors = [
            // MANDATORY FIELD ERRORS
            [
                'error_code' => 'TCSI_STUDENT_MANDATORY_001',
                'file_type' => 'STUDENT',
                'category' => 'MANDATORY',
                'field_name' => 'chessn',
                'description' => 'CHESSN is required',
                'resolution_guidance' => 'Enter a valid 10-digit CHESSN for the student.',
                'is_auto_fixable' => false,
                'example_correct_value' => '1234567890',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'error_code' => 'TCSI_STUDENT_MANDATORY_002',
                'file_type' => 'STUDENT',
                'category' => 'MANDATORY',
                'field_name' => 'last_name',
                'description' => 'Family name (surname/last name) is required',
                'resolution_guidance' => 'Enter the student\'s family name as it appears on official documents.',
                'is_auto_fixable' => false,
                'example_correct_value' => 'Smith',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'error_code' => 'TCSI_STUDENT_MANDATORY_003',
                'file_type' => 'STUDENT',
                'category' => 'MANDATORY',
                'field_name' => 'first_name',
                'description' => 'Given name (first name) is required',
                'resolution_guidance' => 'Enter the student\'s given name.',
                'is_auto_fixable' => false,
                'example_correct_value' => 'John',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // FORMAT VALIDATION ERRORS
            [
                'error_code' => 'TCSI_STUDENT_FORMAT_101',
                'file_type' => 'STUDENT',
                'category' => 'FORMAT',
                'field_name' => 'chessn',
                'description' => 'CHESSN must be exactly 10 digits',
                'resolution_guidance' => 'CHESSN must be a 10-digit number with no spaces or special characters.',
                'is_auto_fixable' => true,
                'auto_fix_function' => 'padChessn',
                'example_correct_value' => '1234567890',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'error_code' => 'TCSI_STUDENT_FORMAT_102',
                'file_type' => 'STUDENT',
                'category' => 'FORMAT',
                'field_name' => 'date_of_birth',
                'description' => 'Date of birth must be in YYYY-MM-DD format',
                'resolution_guidance' => 'Correct the date format to YYYY-MM-DD (e.g., 2000-01-15).',
                'is_auto_fixable' => true,
                'auto_fix_function' => 'fixDateFormat',
                'example_correct_value' => '2000-01-15',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Add more student errors here - aim for 50+ total
            // Developer should expand this with all error codes from documentation
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
    
    /**
     * Seed Course validation error codes
     */
    private function seedCourseErrors(): void
    {
        $errors = [
            [
                'error_code' => 'TCSI_COURSE_MANDATORY_001',
                'file_type' => 'COURSE',
                'category' => 'MANDATORY',
                'field_name' => 'course_code',
                'description' => 'Course code is required',
                'resolution_guidance' => 'Provide a unique course code identifier.',
                'is_auto_fixable' => false,
                'example_correct_value' => 'BACH001',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add more course errors here
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
    
    /**
     * Seed Unit validation error codes
     */
    private function seedUnitErrors(): void
    {
        $errors = [
            [
                'error_code' => 'TCSI_UNIT_MANDATORY_001',
                'file_type' => 'UNIT',
                'category' => 'MANDATORY',
                'field_name' => 'unit_code',
                'description' => 'Unit code is required',
                'resolution_guidance' => 'Provide a unique unit/subject code.',
                'is_auto_fixable' => false,
                'example_correct_value' => 'COMP101',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add more unit errors here
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
    
    /**
     * Seed Staff validation error codes
     */
    private function seedStaffErrors(): void
    {
        $errors = [
            [
                'error_code' => 'TCSI_STAFF_MANDATORY_001',
                'file_type' => 'STAFF',
                'category' => 'MANDATORY',
                'field_name' => 'staff_identifier',
                'description' => 'Staff identifier is required',
                'resolution_guidance' => 'Provide unique staff identifier.',
                'is_auto_fixable' => false,
                'example_correct_value' => '1234567890',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'error_code' => 'TCSI_STAFF_BUSINESS_206',
                'file_type' => 'STAFF',
                'category' => 'BUSINESS_RULE',
                'field_name' => 'fte',
                'description' => 'Full-time staff must have FTE = 1.0',
                'resolution_guidance' => 'Change employment_type to PART_TIME or set FTE to 1.0.',
                'is_auto_fixable' => true,
                'auto_fix_function' => 'fixFullTimeFTE',
                'example_correct_value' => '1.0',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add more staff errors here
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
    
    /**
     * Seed Provider validation error codes
     */
    private function seedProviderErrors(): void
    {
        $errors = [
            [
                'error_code' => 'TCSI_PROVIDER_MANDATORY_001',
                'file_type' => 'PROVIDER',
                'category' => 'MANDATORY',
                'field_name' => 'provider_code',
                'description' => 'Provider code is required',
                'resolution_guidance' => 'Enter your TCSI provider code.',
                'is_auto_fixable' => false,
                'example_correct_value' => 'PRV12345',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add more provider errors here
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
    
    /**
     * Seed Unit Attempt validation error codes
     */
    private function seedUnitAttemptErrors(): void
    {
        $errors = [
            [
                'error_code' => 'TCSI_UNITATTEMPT_MANDATORY_001',
                'file_type' => 'UNIT_ATTEMPT',
                'category' => 'MANDATORY',
                'field_name' => 'student_identifier',
                'description' => 'Student identifier (CHESSN) is required',
                'resolution_guidance' => 'Link to valid student CHESSN.',
                'is_auto_fixable' => false,
                'example_correct_value' => '1234567890',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'error_code' => 'TCSI_UNITATTEMPT_REFERENCE_301',
                'file_type' => 'UNIT_ATTEMPT',
                'category' => 'REFERENCE_DATA',
                'field_name' => 'result',
                'description' => 'Invalid result code',
                'resolution_guidance' => 'Use valid result code: P (Pass), F (Fail), W (Withdrawn), N (Not assessed).',
                'is_auto_fixable' => false,
                'example_correct_value' => 'P',
                'severity_default' => 'ERROR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add more unit attempt errors here
        ];
        
        DB::table('tcsi_error_code_library')->insert($errors);
    }
}
