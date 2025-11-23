<?php

namespace App\Services\TCSI\Validation;

use App\Models\Provider;

/**
 * Provider Validator
 * 
 * Validates provider (institution) records against TCSI requirements.
 */
class ProviderValidator extends BaseValidator
{
    public function validate($provider, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $provider;
        $this->reportingPeriod = $reportingPeriod;
        
        $recordId = $this->getRecordIdentifier();
        
        $this->validateMandatoryFields($recordId);
        $this->validateFormats($recordId);
        
        return $this->getValidationResult();
    }
    
    private function validateMandatoryFields(string $recordId): void
    {
        $this->validateMandatory('provider_code', 'TCSI_PROVIDER_MANDATORY_001', $recordId);
        $this->validateMandatory('provider_name', 'TCSI_PROVIDER_MANDATORY_002', $recordId);
        $this->validateMandatory('campus_name', 'TCSI_PROVIDER_MANDATORY_003', $recordId);
    }
    
    private function validateFormats(string $recordId): void
    {
        // Provider code format: PRV followed by 5 digits
        if (!$this->isEmpty($this->currentRecord->provider_code)) {
            $this->validatePattern('provider_code', '/^PRV\d{5}$/', 'TCSI_PROVIDER_MANDATORY_001', $recordId);
        }
        
        // ABN validation (if provided)
        if (!$this->isEmpty($this->currentRecord->abn)) {
            $this->validateLength('abn', 11, 'TCSI_PROVIDER_MANDATORY_001', $recordId);
            $this->validatePattern('abn', '/^\d{11}$/', 'TCSI_PROVIDER_MANDATORY_001', $recordId);
        }
    }
    
    protected function getRecordIdentifier(): ?string
    {
        return $this->currentRecord->provider_code ?? $this->currentRecord->provider_name ?? 'Unknown Provider';
    }
}
