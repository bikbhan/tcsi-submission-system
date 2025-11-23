<?php

namespace App\Services\TCSI\Validation;

use App\Models\Staff;
use Carbon\Carbon;

/**
 * Staff Validator
 * 
 * Validates staff records against TCSI requirements.
 */
class StaffValidator extends BaseValidator
{
    private const VALID_EMPLOYMENT_TYPES = ['FULL_TIME', 'PART_TIME', 'CASUAL', 'SESSIONAL'];
    private const VALID_STAFF_CATEGORIES = ['ACADEMIC', 'PROFESSIONAL', 'CASUAL'];
    
    public function validate($staff, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $staff;
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
        $this->validateMandatory('staff_identifier', 'TCSI_STAFF_MANDATORY_001', $recordId);
        $this->validateMandatory('employment_start_date', 'TCSI_STAFF_MANDATORY_002', $recordId);
        $this->validateMandatory('position_classification', 'TCSI_STAFF_MANDATORY_003', $recordId);
        $this->validateMandatory('fte', 'TCSI_STAFF_MANDATORY_004', $recordId);
        $this->validateMandatory('employment_type', 'TCSI_STAFF_MANDATORY_005', $recordId);
        $this->validateMandatory('staff_category', 'TCSI_STAFF_MANDATORY_006', $recordId);
    }
    
    private function validateFormats(string $recordId): void
    {
        $this->validateDateFormat('employment_start_date', 'TCSI_STAFF_FORMAT_101', $recordId);
        
        if (!$this->isEmpty($this->currentRecord->employment_end_date)) {
            $this->validateDateFormat('employment_end_date', 'TCSI_STAFF_FORMAT_101', $recordId);
        }
        
        if (!$this->isEmpty($this->currentRecord->fte)) {
            $this->validateNumeric('fte', 'TCSI_STAFF_FORMAT_103', 0.01, 1.0, $recordId);
        }
    }
    
    private function validateReferenceData(string $recordId): void
    {
        $this->validateInList('employment_type', self::VALID_EMPLOYMENT_TYPES, 'TCSI_STAFF_REFERENCE_303', $recordId);
        $this->validateInList('staff_category', self::VALID_STAFF_CATEGORIES, 'TCSI_STAFF_REFERENCE_303', $recordId);
    }
    
    private function validateBusinessRules(string $recordId): void
    {
        // End date after start date
        if (!$this->isEmpty($this->currentRecord->employment_start_date) && 
            !$this->isEmpty($this->currentRecord->employment_end_date)) {
            
            $startDate = Carbon::parse($this->currentRecord->employment_start_date);
            $endDate = Carbon::parse($this->currentRecord->employment_end_date);
            
            if ($endDate->lessThan($startDate)) {
                $this->addError('TCSI_STAFF_BUSINESS_201', 'employment_end_date', $this->currentRecord->employment_end_date, $recordId);
            }
        }
        
        // Full-time staff must have FTE = 1.0
        if (!$this->isEmpty($this->currentRecord->fte) && 
            !$this->isEmpty($this->currentRecord->employment_type)) {
            
            $fte = (float)$this->currentRecord->fte;
            $employmentType = $this->currentRecord->employment_type;
            
            if ($employmentType === 'FULL_TIME' && $fte !== 1.0) {
                $this->addError('TCSI_STAFF_BUSINESS_206', 'fte', $this->formatValue($fte), $recordId);
            }
        }
        
        // Check for duplicate staff identifier
        if (!$this->isEmpty($this->currentRecord->staff_identifier)) {
            $query = Staff::where('staff_identifier', $this->currentRecord->staff_identifier);
            
            if ($this->currentRecord->id) {
                $query->where('id', '!=', $this->currentRecord->id);
            }
            
            if ($query->exists()) {
                $this->addError('TCSI_STAFF_BUSINESS_201', 'staff_identifier', $this->currentRecord->staff_identifier, $recordId);
            }
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        if (!$this->isEmpty($this->currentRecord->staff_identifier)) {
            return $this->currentRecord->staff_identifier;
        }
        
        $name = trim(($this->currentRecord->first_name ?? '') . ' ' . ($this->currentRecord->last_name ?? ''));
        return !empty($name) ? $name : 'Unknown Staff';
    }
}
