<?php

namespace App\Services\TCSI\Validation;

use App\Models\Course;
use Carbon\Carbon;

/**
 * Course Validator
 * 
 * Validates course records against TCSI requirements.
 */
class CourseValidator extends BaseValidator
{
    private const VALID_QUALIFICATION_LEVELS = [
        '020', '030', '040', '050', '060', '070', '080', '090', '100'
    ];
    
    private const VALID_ATTENDANCE_MODES = ['I', 'E', 'M', 'O'];
    
    public function validate($course, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $course;
        $this->reportingPeriod = $reportingPeriod;
        
        $recordId = $this->getRecordIdentifier();
        
        $this->validateMandatoryFields($recordId);
        $this->validateFormats($recordId);
        $this->validateReferenceData($recordId);
        $this->validateBusinessRules($recordId);
        
        return $this->getValidationResult();
    }
    
    private function validateMandatoryFields(string $recordId): void
    {
        $this->validateMandatory('course_code', 'TCSI_COURSE_MANDATORY_001', $recordId);
        $this->validateMandatory('course_name', 'TCSI_COURSE_MANDATORY_002', $recordId);
        $this->validateMandatory('qualification_level', 'TCSI_COURSE_MANDATORY_003', $recordId);
        $this->validateMandatory('field_of_education', 'TCSI_COURSE_MANDATORY_004', $recordId);
        $this->validateMandatory('course_duration', 'TCSI_COURSE_MANDATORY_005', $recordId);
        $this->validateMandatory('total_eftsl', 'TCSI_COURSE_MANDATORY_006', $recordId);
    }
    
    private function validateFormats(string $recordId): void
    {
        if (!$this->isEmpty($this->currentRecord->course_code)) {
            if (!preg_match('/^[A-Z0-9\-]+$/i', $this->currentRecord->course_code)) {
                $this->addError('TCSI_COURSE_FORMAT_101', 'course_code', $this->currentRecord->course_code, $recordId);
            }
        }
        
        if (!$this->isEmpty($this->currentRecord->field_of_education)) {
            $this->validateLength('field_of_education', 6, 'TCSI_COURSE_FORMAT_102', $recordId);
            $this->validatePattern('field_of_education', '/^\d{6}$/', 'TCSI_COURSE_FORMAT_102', $recordId);
        }
        
        if (!$this->isEmpty($this->currentRecord->course_duration)) {
            $this->validateNumeric('course_duration', 'TCSI_COURSE_FORMAT_103', 0.25, 10.0, $recordId);
        }
    }
    
    private function validateReferenceData(string $recordId): void
    {
        $this->validateInList('qualification_level', self::VALID_QUALIFICATION_LEVELS, 'TCSI_COURSE_REFERENCE_301', $recordId);
        
        if (!$this->isEmpty($this->currentRecord->attendance_mode)) {
            $this->validateInList('attendance_mode', self::VALID_ATTENDANCE_MODES, 'TCSI_COURSE_REFERENCE_303', $recordId);
        }
    }
    
    private function validateBusinessRules(string $recordId): void
    {
        // Check for duplicate course code
        if (!$this->isEmpty($this->currentRecord->course_code)) {
            $query = Course::where('course_code', $this->currentRecord->course_code);
            
            if ($this->currentRecord->id) {
                $query->where('id', '!=', $this->currentRecord->id);
            }
            
            if ($query->exists()) {
                $this->addError('TCSI_COURSE_BUSINESS_201', 'course_code', $this->currentRecord->course_code, $recordId);
            }
        }
        
        // End date after start date
        if (!$this->isEmpty($this->currentRecord->course_start_date) && 
            !$this->isEmpty($this->currentRecord->course_end_date)) {
            
            $startDate = Carbon::parse($this->currentRecord->course_start_date);
            $endDate = Carbon::parse($this->currentRecord->course_end_date);
            
            if ($endDate->lessThan($startDate)) {
                $this->addError('TCSI_COURSE_BUSINESS_202', 'course_end_date', $this->currentRecord->course_end_date, $recordId);
            }
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        return $this->currentRecord->course_code ?? $this->currentRecord->course_name ?? 'Unknown Course';
    }
}
