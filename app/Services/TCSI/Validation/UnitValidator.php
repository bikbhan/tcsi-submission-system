<?php

namespace App\Services\TCSI\Validation;

use App\Models\Unit;

/**
 * Unit Validator
 * 
 * Validates unit (subject) records against TCSI requirements.
 */
class UnitValidator extends BaseValidator
{
    private const MIN_CREDIT_POINTS = 3;
    private const MAX_CREDIT_POINTS = 50;
    
    public function validate($unit, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $unit;
        $this->reportingPeriod = $reportingPeriod;
        
        $recordId = $this->getRecordIdentifier();
        
        $this->validateMandatoryFields($recordId);
        $this->validateFormats($recordId);
        $this->validateBusinessRules($recordId);
        
        return $this->getValidationResult();
    }
    
    private function validateMandatoryFields(string $recordId): void
    {
        $this->validateMandatory('unit_code', 'TCSI_UNIT_MANDATORY_001', $recordId);
        $this->validateMandatory('unit_name', 'TCSI_UNIT_MANDATORY_002', $recordId);
        $this->validateMandatory('credit_points', 'TCSI_UNIT_MANDATORY_003', $recordId);
        $this->validateMandatory('unit_level', 'TCSI_UNIT_MANDATORY_004', $recordId);
        $this->validateMandatory('field_of_education', 'TCSI_UNIT_MANDATORY_005', $recordId);
    }
    
    private function validateFormats(string $recordId): void
    {
        if (!$this->isEmpty($this->currentRecord->unit_code)) {
            if (!preg_match('/^[A-Z0-9\-]+$/i', $this->currentRecord->unit_code)) {
                $this->addError('TCSI_UNIT_FORMAT_101', 'unit_code', $this->currentRecord->unit_code, $recordId);
            }
        }
        
        if (!$this->isEmpty($this->currentRecord->credit_points)) {
            $this->validateNumeric('credit_points', 'TCSI_UNIT_FORMAT_102', self::MIN_CREDIT_POINTS, self::MAX_CREDIT_POINTS, $recordId);
        }
    }
    
    private function validateBusinessRules(string $recordId): void
    {
        // Check for duplicate unit code
        if (!$this->isEmpty($this->currentRecord->unit_code)) {
            $query = Unit::where('unit_code', $this->currentRecord->unit_code);
            
            if ($this->currentRecord->id) {
                $query->where('id', '!=', $this->currentRecord->id);
            }
            
            if ($query->exists()) {
                $this->addError('TCSI_UNIT_BUSINESS_201', 'unit_code', $this->currentRecord->unit_code, $recordId);
            }
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        return $this->currentRecord->unit_code ?? $this->currentRecord->unit_name ?? 'Unknown Unit';
    }
}
