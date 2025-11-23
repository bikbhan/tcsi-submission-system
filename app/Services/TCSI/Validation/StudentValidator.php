<?php

namespace App\Services\TCSI\Validation;

use App\Models\Student;
use App\Models\Course;
use Carbon\Carbon;

/**
 * Student Validator
 * 
 * Validates student records against TCSI requirements.
 * Covers: demographics, enrolment, dates, business logic
 */
class StudentValidator extends BaseValidator
{
    private const VALID_GENDERS = ['M', 'F', 'X'];
    private const VALID_INDIGENOUS_STATUS = ['1', '2', '3', '4'];
    private const VALID_CITIZENSHIP_STATUS = ['A', 'P', 'I', 'T'];
    private const VALID_STUDY_MODES = ['F', 'P', 'E'];
    private const VALID_ATTENDANCE_TYPES = ['I', 'E', 'M', 'O'];
    private const MIN_AGE = 15;
    
    public function validate($student, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $student;
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
        $this->validateMandatory('chessn', 'TCSI_STUDENT_MANDATORY_001', $recordId);
        $this->validateMandatory('last_name', 'TCSI_STUDENT_MANDATORY_002', $recordId);
        $this->validateMandatory('first_name', 'TCSI_STUDENT_MANDATORY_003', $recordId);
        $this->validateMandatory('date_of_birth', 'TCSI_STUDENT_MANDATORY_004', $recordId);
        $this->validateMandatory('gender', 'TCSI_STUDENT_MANDATORY_005', $recordId);
        $this->validateMandatory('country_of_birth', 'TCSI_STUDENT_MANDATORY_006', $recordId);
        $this->validateMandatory('indigenous_status', 'TCSI_STUDENT_MANDATORY_007', $recordId);
        $this->validateMandatory('citizenship_status', 'TCSI_STUDENT_MANDATORY_008', $recordId);
        
        $citizenshipStatus = $this->currentRecord->citizenship_status ?? null;
        if (in_array($citizenshipStatus, ['A', 'P', 'T'])) {
            $this->validateMandatory('residential_postcode', 'TCSI_STUDENT_MANDATORY_009', $recordId);
        }
        
        $this->validateMandatory('highest_education_level', 'TCSI_STUDENT_MANDATORY_010', $recordId);
        $this->validateMandatory('course_code', 'TCSI_STUDENT_MANDATORY_011', $recordId);
        $this->validateMandatory('commencement_date', 'TCSI_STUDENT_MANDATORY_012', $recordId);
        $this->validateMandatory('study_mode', 'TCSI_STUDENT_MANDATORY_013', $recordId);
        $this->validateMandatory('attendance_type', 'TCSI_STUDENT_MANDATORY_014', $recordId);
        $this->validateMandatory('basis_for_admission', 'TCSI_STUDENT_MANDATORY_015', $recordId);
    }
    
    private function validateFormats(string $recordId): void
    {
        if (!$this->isEmpty($this->currentRecord->chessn)) {
            $this->validateLength('chessn', 10, 'TCSI_STUDENT_FORMAT_101', $recordId);
            $this->validatePattern('chessn', '/^\d{10}$/', 'TCSI_STUDENT_FORMAT_101', $recordId);
        }
        
        $this->validateDateFormat('date_of_birth', 'TCSI_STUDENT_FORMAT_102', $recordId);
        $this->validateDateFormat('commencement_date', 'TCSI_STUDENT_FORMAT_103', $recordId);
        
        if (!$this->isEmpty($this->currentRecord->email)) {
            $this->validateEmail('email', 'TCSI_STUDENT_FORMAT_104', $recordId);
        }
        
        if (!$this->isEmpty($this->currentRecord->residential_postcode)) {
            $this->validateLength('residential_postcode', 4, 'TCSI_STUDENT_FORMAT_106', $recordId);
            $this->validatePattern('residential_postcode', '/^\d{4}$/', 'TCSI_STUDENT_FORMAT_106', $recordId);
        }
        
        if (!$this->isEmpty($this->currentRecord->eftsl)) {
            $this->validateNumeric('eftsl', 'TCSI_STUDENT_FORMAT_107', 0.01, 1.0, $recordId);
        }
    }
    
    private function validateReferenceData(string $recordId): void
    {
        $this->validateInList('gender', self::VALID_GENDERS, 'TCSI_STUDENT_REFERENCE_301', $recordId);
        $this->validateInList('indigenous_status', self::VALID_INDIGENOUS_STATUS, 'TCSI_STUDENT_REFERENCE_303', $recordId);
        $this->validateInList('citizenship_status', self::VALID_CITIZENSHIP_STATUS, 'TCSI_STUDENT_REFERENCE_304', $recordId);
        
        if (!$this->isEmpty($this->currentRecord->course_code)) {
            $courseExists = Course::where('course_code', $this->currentRecord->course_code)->exists();
            if (!$courseExists) {
                $this->addError('TCSI_STUDENT_REFERENCE_306', 'course_code', $this->currentRecord->course_code, $recordId);
            }
        }
        
        $this->validateInList('study_mode', self::VALID_STUDY_MODES, 'TCSI_STUDENT_REFERENCE_307', $recordId);
        $this->validateInList('attendance_type', self::VALID_ATTENDANCE_TYPES, 'TCSI_STUDENT_REFERENCE_308', $recordId);
    }
    
    private function validateBusinessRules(string $recordId): void
    {
        if (!$this->isEmpty($this->currentRecord->date_of_birth)) {
            $dob = Carbon::parse($this->currentRecord->date_of_birth);
            $age = $dob->age;
            
            if ($age < self::MIN_AGE) {
                $this->addError('TCSI_STUDENT_BUSINESS_201', 'date_of_birth', $this->currentRecord->date_of_birth, $recordId);
            }
            
            if ($dob->isFuture()) {
                $this->addError('TCSI_STUDENT_BUSINESS_202', 'date_of_birth', $this->currentRecord->date_of_birth, $recordId);
            }
        }
        
        if (!$this->isEmpty($this->currentRecord->date_of_birth) && 
            !$this->isEmpty($this->currentRecord->commencement_date)) {
            
            $dob = Carbon::parse($this->currentRecord->date_of_birth);
            $commDate = Carbon::parse($this->currentRecord->commencement_date);
            
            if ($commDate->lessThan($dob)) {
                $this->addError('TCSI_STUDENT_BUSINESS_203', 'commencement_date', $this->currentRecord->commencement_date, $recordId);
            }
        }
        
        if ($this->currentRecord->study_mode === 'F' && 
            !$this->isEmpty($this->currentRecord->eftsl) &&
            $this->currentRecord->eftsl < 0.75) {
            
            $this->addError('TCSI_STUDENT_BUSINESS_205', 'study_mode', $this->currentRecord->study_mode, $recordId);
        }
        
        if (!$this->isEmpty($this->currentRecord->eftsl) && $this->currentRecord->eftsl > 1.0) {
            $this->addError('TCSI_STUDENT_BUSINESS_206', 'eftsl', $this->currentRecord->eftsl, $recordId);
        }
        
        if ($this->currentRecord->citizenship_status === 'I') {
            if ($this->currentRecord->commonwealth_supported ?? false) {
                $this->addError('TCSI_STUDENT_BUSINESS_207', 'citizenship_status', $this->currentRecord->citizenship_status, $recordId);
            }
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        if (!$this->isEmpty($this->currentRecord->chessn)) {
            return $this->currentRecord->chessn;
        }
        
        $name = trim(($this->currentRecord->first_name ?? '') . ' ' . ($this->currentRecord->last_name ?? ''));
        return !empty($name) ? $name : 'Unknown Student';
    }
}
