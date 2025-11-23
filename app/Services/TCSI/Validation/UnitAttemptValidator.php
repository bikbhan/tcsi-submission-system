<?php

namespace App\Services\TCSI\Validation;

use App\Models\UnitAttempt;
use App\Models\Student;
use App\Models\Unit;

/**
 * Unit Attempt Validator
 * 
 * Validates unit attempt (enrolment result) records.
 */
class UnitAttemptValidator extends BaseValidator
{
    private const VALID_RESULTS = [
        'P', 'F', 'W', 'N', 'WD', 'WF', 'HD', 'D', 'C', 'PC', 'SA', 'US'
    ];
    
    public function validate($unitAttempt, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $unitAttempt;
        $this->reportingPeriod = $reportingPeriod;
        
        $recordId = $this->getRecordIdentifier();
        
        $this->validateMandatoryFields($recordId);
        $this->validateReferenceData($recordId);
        $this->validateBusinessRules($recordId);
        
        return $this->getValidationResult();
    }
    
    private function validateMandatoryFields(string $recordId): void
    {
        $this->validateMandatory('student_identifier', 'TCSI_UNITATTEMPT_MANDATORY_001', $recordId);
        $this->validateMandatory('unit_code', 'TCSI_UNITATTEMPT_MANDATORY_002', $recordId);
        $this->validateMandatory('study_period', 'TCSI_UNITATTEMPT_MANDATORY_003', $recordId);
        $this->validateMandatory('result', 'TCSI_UNITATTEMPT_MANDATORY_004', $recordId);
    }
    
    private function validateReferenceData(string $recordId): void
    {
        $this->validateInList('result', self::VALID_RESULTS, 'TCSI_UNITATTEMPT_REFERENCE_301', $recordId);
    }
    
    private function validateBusinessRules(string $recordId): void
    {
        // Validate student exists
        if (!$this->isEmpty($this->currentRecord->student_identifier)) {
            $studentExists = Student::where('chessn', $this->currentRecord->student_identifier)->exists();
            if (!$studentExists) {
                $this->addError('TCSI_UNITATTEMPT_BUSINESS_201', 'student_identifier', $this->currentRecord->student_identifier, $recordId);
            }
        }
        
        // Validate unit exists
        if (!$this->isEmpty($this->currentRecord->unit_code)) {
            $unitExists = Unit::where('unit_code', $this->currentRecord->unit_code)->exists();
            if (!$unitExists) {
                $this->addError('TCSI_UNITATTEMPT_BUSINESS_202', 'unit_code', $this->currentRecord->unit_code, $recordId);
            }
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        $student = $this->currentRecord->student_identifier ?? 'Unknown';
        $unit = $this->currentRecord->unit_code ?? 'Unknown';
        return "{$student} - {$unit}";
    }
}
