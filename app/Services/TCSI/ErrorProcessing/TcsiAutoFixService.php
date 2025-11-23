<?php

namespace App\Services\TCSI\ErrorProcessing;

use App\Models\TCSI\TcsiError;
use App\Models\Student;
use App\Models\Course;
use App\Models\Unit;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TCSI Auto-Fix Service
 * 
 * Automatically fixes common validation errors.
 */
class TcsiAutoFixService
{
    /**
     * Attempt to automatically fix an error
     */
    public function attemptFix(int $errorId): array
    {
        $error = TcsiError::with('errorCodeDefinition')->find($errorId);
        
        if (!$error) {
            return ['success' => false, 'message' => 'Error not found'];
        }
        
        if (!$error->errorCodeDefinition || !$error->errorCodeDefinition->is_auto_fixable) {
            return ['success' => false, 'message' => 'This error cannot be automatically fixed'];
        }
        
        $fixFunction = $error->errorCodeDefinition->auto_fix_function;
        
        if (!method_exists($this, $fixFunction)) {
            return ['success' => false, 'message' => "Fix function '{$fixFunction}' not implemented"];
        }
        
        try {
            DB::beginTransaction();
            
            $result = $this->$fixFunction($error);
            
            if ($result['success']) {
                $error->update([
                    'resolution_status' => 'RESOLVED',
                    'resolution_action' => $result['action_taken'],
                    'auto_fix_attempted' => true,
                    'auto_fix_success' => true,
                    'resolved_by_user_id' => auth()->id() ?? 0,
                    'resolved_at' => now()
                ]);
                
                DB::commit();
            } else {
                DB::rollBack();
            }
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Auto-fix failed for error {$errorId}", [
                'error_code' => $error->error_code,
                'exception' => $e->getMessage()
            ]);
            
            $error->update([
                'auto_fix_attempted' => true,
                'auto_fix_success' => false,
                'resolution_notes' => 'Auto-fix failed: ' . $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Auto-fix failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Attempt to fix multiple errors at once
     */
    public function bulkFix(array $errorIds): array
    {
        $results = [
            'total' => count($errorIds),
            'fixed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($errorIds as $errorId) {
            $result = $this->attemptFix($errorId);
            
            if ($result['success']) {
                $results['fixed']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][$errorId] = $result;
        }
        
        return $results;
    }
    
    // ============================================================
    // AUTO-FIX FUNCTIONS
    // ============================================================
    
    /**
     * Fix CHESSN by padding to 10 digits
     */
    private function padChessn(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->chessn;
        $cleaned = preg_replace('/[^0-9]/', '', $originalValue);
        $fixed = str_pad($cleaned, 10, '0', STR_PAD_LEFT);
        
        if (strlen($fixed) !== 10 || !is_numeric($fixed)) {
            return ['success' => false, 'message' => "Cannot pad CHESSN '{$originalValue}' to valid format"];
        }
        
        $record->chessn = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Padded CHESSN from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    /**
     * Fix date format to YYYY-MM-DD
     */
    private function fixDateFormat(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        $fieldName = $error->field_name;
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->$fieldName;
        
        if (empty($originalValue)) {
            return ['success' => false, 'message' => 'Date field is empty'];
        }
        
        $formats = ['d/m/Y', 'd-m-Y', 'd/m/y', 'Y-m-d', 'd M Y', 'd.m.Y'];
        $parsedDate = null;
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $originalValue);
            if ($date && $date->format($format) === $originalValue) {
                $parsedDate = $date;
                break;
            }
        }
        
        if (!$parsedDate) {
            return ['success' => false, 'message' => "Cannot parse date '{$originalValue}'"];
        }
        
        $fixed = $parsedDate->format('Y-m-d');
        
        $record->$fieldName = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Converted date from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    /**
     * Fix phone number to Australian format
     */
    private function fixPhoneFormat(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->phone;
        $cleaned = preg_replace('/[^0-9]/', '', $originalValue);
        
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 2) === '61') {
            $cleaned = '0' . substr($cleaned, 2);
        }
        
        if (strlen($cleaned) === 9 && substr($cleaned, 0, 1) !== '0') {
            $cleaned = '0' . $cleaned;
        }
        
        if (strlen($cleaned) !== 10 || substr($cleaned, 0, 1) !== '0') {
            return ['success' => false, 'message' => "Cannot convert '{$originalValue}' to valid phone format"];
        }
        
        $record->phone = $cleaned;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Formatted phone from '{$originalValue}' to '{$cleaned}'",
            'original_value' => $originalValue,
            'new_value' => $cleaned
        ];
    }
    
    /**
     * Fix postcode by padding to 4 digits
     */
    private function padPostcode(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->residential_postcode;
        $cleaned = preg_replace('/[^0-9]/', '', $originalValue);
        $fixed = str_pad($cleaned, 4, '0', STR_PAD_LEFT);
        
        if (strlen($fixed) !== 4 || !is_numeric($fixed)) {
            return ['success' => false, 'message' => "Cannot pad postcode '{$originalValue}' to valid format"];
        }
        
        $record->residential_postcode = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Padded postcode from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    /**
     * Fix full-time staff FTE to 1.0
     */
    private function fixFullTimeFTE(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->fte;
        
        if ($record->employment_type !== 'FULL_TIME') {
            return ['success' => false, 'message' => 'Employment type is not FULL_TIME'];
        }
        
        $record->fte = 1.0;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Set FTE from '{$originalValue}' to '1.0' for full-time employment",
            'original_value' => $originalValue,
            'new_value' => 1.0
        ];
    }
    
    /**
     * Sanitize course code
     */
    private function sanitizeCourseCode(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->course_code;
        $fixed = preg_replace('/[^A-Za-z0-9\-]/', '', $originalValue);
        $fixed = strtoupper($fixed);
        
        if (empty($fixed)) {
            return ['success' => false, 'message' => "Cannot sanitize empty course code"];
        }
        
        $record->course_code = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Sanitized course code from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    /**
     * Sanitize unit code
     */
    private function sanitizeUnitCode(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->unit_code;
        $fixed = preg_replace('/[^A-Za-z0-9\-]/', '', $originalValue);
        $fixed = strtoupper($fixed);
        
        if (empty($fixed)) {
            return ['success' => false, 'message' => "Cannot sanitize empty unit code"];
        }
        
        $record->unit_code = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Sanitized unit code from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    /**
     * Pad ASCED code to 6 digits
     */
    private function padAscedCode(TcsiError $error): array
    {
        $record = $this->getRecord($error);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Record not found'];
        }
        
        $originalValue = $record->field_of_education;
        $cleaned = preg_replace('/[^0-9]/', '', $originalValue);
        $fixed = str_pad($cleaned, 6, '0', STR_PAD_LEFT);
        
        if (strlen($fixed) !== 6 || !is_numeric($fixed)) {
            return ['success' => false, 'message' => "Cannot pad ASCED code '{$originalValue}' to valid format"];
        }
        
        $record->field_of_education = $fixed;
        $record->save();
        
        return [
            'success' => true,
            'action_taken' => "Padded ASCED code from '{$originalValue}' to '{$fixed}'",
            'original_value' => $originalValue,
            'new_value' => $fixed
        ];
    }
    
    // ============================================================
    // HELPER METHODS
    // ============================================================
    
    /**
     * Get the actual record from error
     */
    private function getRecord(TcsiError $error)
    {
        if (!$error->item_type || !$error->item_id) {
            return null;
        }
        
        $modelMap = [
            'STUDENT' => Student::class,
            'COURSE' => Course::class,
            'UNIT' => Unit::class,
            'STAFF' => Staff::class,
        ];
        
        $modelClass = $modelMap[$error->item_type] ?? null;
        
        if (!$modelClass) {
            return null;
        }
        
        return $modelClass::find($error->item_id);
    }
}
