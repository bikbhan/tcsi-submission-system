<?php

namespace App\Services\TCSI\Validation;

use App\Models\TCSI\TcsiErrorCodeLibrary;
use Illuminate\Support\Facades\Cache;

/**
 * Base Validator Class
 * 
 * Provides common validation functionality for all TCSI validators.
 * All file type validators extend this base class.
 */
abstract class BaseValidator
{
    protected array $errorLibrary = [];
    protected array $errors = [];
    protected array $warnings = [];
    protected $currentRecord;
    protected string $reportingPeriod;
    
    public function __construct()
    {
        $this->loadErrorLibrary();
    }
    
    /**
     * Abstract validate method - must be implemented by child classes
     */
    abstract public function validate($record, string $reportingPeriod): array;
    
    /**
     * Load error code library from database (with caching)
     */
    protected function loadErrorLibrary(): void
    {
        $this->errorLibrary = Cache::remember('tcsi_error_library', 3600, function () {
            return TcsiErrorCodeLibrary::all()->keyBy('error_code')->toArray();
        });
    }
    
    /**
     * Add an error using error code
     */
    protected function addError(
        string $errorCode, 
        ?string $fieldName = null, 
        $submittedValue = null,
        ?string $recordIdentifier = null
    ): void {
        $errorDef = $this->errorLibrary[$errorCode] ?? null;
        
        if (!$errorDef) {
            $this->errors[] = [
                'error_code' => $errorCode,
                'field_name' => $fieldName,
                'error_message' => "Validation error: {$errorCode}",
                'severity' => 'ERROR',
                'submitted_value' => $this->formatValue($submittedValue),
                'record_identifier' => $recordIdentifier,
                'resolution_guidance' => 'Please check the field value',
                'is_auto_fixable' => false,
            ];
            return;
        }
        
        $error = [
            'error_code' => $errorCode,
            'field_name' => $fieldName ?? $errorDef['field_name'],
            'error_message' => $errorDef['description'],
            'severity' => $errorDef['severity_default'],
            'submitted_value' => $this->formatValue($submittedValue),
            'expected_format' => $errorDef['example_correct_value'],
            'record_identifier' => $recordIdentifier,
            'resolution_guidance' => $errorDef['resolution_guidance'],
            'is_auto_fixable' => $errorDef['is_auto_fixable'],
            'auto_fix_function' => $errorDef['auto_fix_function'],
            'example_correct_value' => $errorDef['example_correct_value']
        ];
        
        if ($errorDef['severity_default'] === 'ERROR') {
            $this->errors[] = $error;
        } else {
            $this->warnings[] = $error;
        }
    }
    
    /**
     * Check if field is empty/null
     */
    protected function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }
        
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate mandatory field
     */
    protected function validateMandatory(
        string $fieldName, 
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate date format (YYYY-MM-DD)
     */
    protected function validateDateFormat(
        string $fieldName,
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        $parts = explode('-', $value);
        if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate field is in allowed values list
     */
    protected function validateInList(
        string $fieldName,
        array $allowedValues,
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (!in_array($value, $allowedValues, true)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate numeric field
     */
    protected function validateNumeric(
        string $fieldName,
        string $errorCode,
        ?float $min = null,
        ?float $max = null,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (!is_numeric($value)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        $numValue = (float)$value;
        
        if ($min !== null && $numValue < $min) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        if ($max !== null && $numValue > $max) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email format
     */
    protected function validateEmail(
        string $fieldName,
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate string length
     */
    protected function validateLength(
        string $fieldName,
        int $exactLength,
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (strlen($value) !== $exactLength) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate regex pattern
     */
    protected function validatePattern(
        string $fieldName,
        string $pattern,
        string $errorCode,
        ?string $recordIdentifier = null
    ): bool {
        $value = $this->currentRecord->$fieldName ?? null;
        
        if ($this->isEmpty($value)) {
            return true;
        }
        
        if (!preg_match($pattern, $value)) {
            $this->addError($errorCode, $fieldName, $value, $recordIdentifier);
            return false;
        }
        
        return true;
    }
    
    /**
     * Format value for display
     */
    protected function formatValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_array($value)) {
            return json_encode($value);
        }
        
        return (string)$value;
    }
    
    /**
     * Get validation result
     */
    protected function getValidationResult(): array
    {
        return [
            'valid' => empty($this->errors),
            'has_errors' => !empty($this->errors),
            'has_warnings' => !empty($this->warnings),
            'error_count' => count($this->errors),
            'warning_count' => count($this->warnings),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'all_issues' => array_merge($this->errors, $this->warnings)
        ];
    }
    
    /**
     * Reset validator state
     */
    protected function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->currentRecord = null;
    }
    
    /**
     * Get record identifier - override in child classes
     */
    protected function getRecordIdentifier(): ?string
    {
        return null;
    }
}
