# TCSI Error Codes Reference

## Overview

This system uses 130+ predefined error codes covering all TCSI validation requirements.

## Error Code Structure
```
TCSI_[FILE_TYPE]_[CATEGORY]_[NUMBER]
```

### Examples
- `TCSI_STUDENT_MANDATORY_001` - Student CHESSN is required
- `TCSI_COURSE_FORMAT_101` - Course code format invalid
- `TCSI_STAFF_BUSINESS_206` - FTE doesn't match employment type

## Categories

### MANDATORY
Required fields that must be present.

**Example:**
```php
TCSI_STUDENT_MANDATORY_001: CHESSN is required
Resolution: Enter a valid 10-digit CHESSN
Auto-fixable: No
```

### FORMAT
Incorrect data format (dates, numbers, codes).

**Example:**
```php
TCSI_STUDENT_FORMAT_101: CHESSN must be 10 digits
Resolution: Pad or trim CHESSN to exactly 10 digits
Auto-fixable: Yes (padChessn function)
```

### BUSINESS_RULE
Business logic violations (age limits, date logic).

**Example:**
```php
TCSI_STUDENT_BUSINESS_201: Student age < 15 years
Resolution: Verify date of birth is correct
Auto-fixable: No
```

### REFERENCE_DATA
Invalid codes from lookup tables.

**Example:**
```php
TCSI_STUDENT_REFERENCE_301: Invalid gender code
Resolution: Use M, F, or X
Auto-fixable: No
```

## Error Counts by File Type

| File Type | Mandatory | Format | Business Rule | Reference Data | Total |
|-----------|-----------|--------|---------------|----------------|-------|
| Student | 15 | 7 | 10 | 10 | 42+ |
| Course | 6 | 4 | 4 | 3 | 17+ |
| Unit | 5 | 2 | 2 | 1 | 10+ |
| Staff | 6 | 3 | 6 | 3 | 18+ |
| Provider | 3 | 2 | 0 | 0 | 5+ |
| Unit Attempt | 4 | 0 | 3 | 1 | 8+ |
| **TOTAL** | | | | | **100+** |

## Auto-Fixable Errors

### Format Fixes
- `TCSI_STUDENT_FORMAT_101` - Pad CHESSN to 10 digits
- `TCSI_STUDENT_FORMAT_102` - Convert date to YYYY-MM-DD
- `TCSI_STUDENT_FORMAT_105` - Format phone number
- `TCSI_STUDENT_FORMAT_106` - Pad postcode to 4 digits
- `TCSI_COURSE_FORMAT_101` - Sanitize course code
- `TCSI_UNIT_FORMAT_101` - Sanitize unit code
- `TCSI_STAFF_BUSINESS_206` - Fix FTE for full-time staff

### Auto-Fix Functions
```php
padChessn()         // 1234567 → 0001234567
fixDateFormat()     // 15/01/2000 → 2000-01-15
fixPhoneFormat()    // (04) 1234 5678 → 0412345678
padPostcode()       // 800 → 0800
sanitizeCourseCode()  // BACH 001 → BACH001
sanitizeUnitCode()    // COMP-101! → COMP-101
fixFullTimeFTE()    // FTE 0.8 → 1.0 (if FULL_TIME)
padAscedCode()      // 8011 → 080110
```

## Using Error Codes in Validators

### Example: Student Validator
```php
// Mandatory field check
if (empty($student->chessn)) {
    $this->addError(
        'TCSI_STUDENT_MANDATORY_001',
        'chessn',
        null,
        $studentId
    );
}

// Format check
if (strlen($student->chessn) !== 10) {
    $this->addError(
        'TCSI_STUDENT_FORMAT_101',
        'chessn',
        $student->chessn,
        $studentId
    );
}

// Business rule check
if ($student->age < 15) {
    $this->addError(
        'TCSI_STUDENT_BUSINESS_201',
        'date_of_birth',
        $student->date_of_birth,
        $studentId
    );
}
```

## Error Severity

### ERROR (Blocking)
Must be fixed before submission to TCSI.

**Examples:**
- Missing mandatory fields
- Invalid date formats
- Failed business rules

### WARNING (Non-blocking)
Should be reviewed but won't block submission.

**Examples:**
- Unusual credit point values
- Non-standard unit levels
- Inconsistent qualification names

## Common Error Patterns

### Pattern 1: Missing Mandatory Data
```
Error: TCSI_STUDENT_MANDATORY_001
Field: chessn
Value: null
Fix: Enter valid 10-digit CHESSN
```

### Pattern 2: Format Issues
```
Error: TCSI_STUDENT_FORMAT_102
Field: date_of_birth
Value: "15/01/2000"
Fix: Change to "2000-01-15"
Auto-fix: Yes
```

### Pattern 3: Business Rule Violation
```
Error: TCSI_STUDENT_BUSINESS_206
Field: eftsl
Value: 1.5
Fix: EFTSL cannot exceed 1.0
Auto-fix: No
```

### Pattern 4: Invalid Reference Data
```
Error: TCSI_STUDENT_REFERENCE_301
Field: gender
Value: "Male"
Fix: Use code: M, F, or X
Auto-fix: No
```

## Querying Error Codes

### Find Error by Code
```php
$error = TcsiErrorCodeLibrary::where('error_code', 'TCSI_STUDENT_MANDATORY_001')->first();

echo $error->description;
echo $error->resolution_guidance;
echo $error->is_auto_fixable ? 'Yes' : 'No';
```

### Find All Auto-Fixable Errors
```php
$autoFixable = TcsiErrorCodeLibrary::where('is_auto_fixable', true)->get();
```

### Find Errors by File Type
```php
$studentErrors = TcsiErrorCodeLibrary::where('file_type', 'STUDENT')->get();
```

### Find Errors by Category
```php
$mandatoryErrors = TcsiErrorCodeLibrary::where('category', 'MANDATORY')->get();
```

## Adding New Error Codes

### Step 1: Add to Seeder
```php
[
    'error_code' => 'TCSI_STUDENT_CUSTOM_999',
    'file_type' => 'STUDENT',
    'category' => 'BUSINESS_RULE',
    'field_name' => 'custom_field',
    'description' => 'Custom validation rule failed',
    'resolution_guidance' => 'How to fix this error',
    'is_auto_fixable' => false,
    'example_correct_value' => 'Example',
    'severity_default' => 'ERROR',
]
```

### Step 2: Use in Validator
```php
if ($customValidationFails) {
    $this->addError('TCSI_STUDENT_CUSTOM_999', 'custom_field', $value, $recordId);
}
```

### Step 3: Add Auto-Fix (If Applicable)
```php
private function fixCustomError(TcsiError $error): array
{
    // Fix logic here
    return [
        'success' => true,
        'action_taken' => 'Fixed custom error',
        'new_value' => $fixedValue
    ];
}
```

## Testing Error Codes
```bash
php artisan tinker
```
```php
// Get error definition
$error = App\Models\TCSI\TcsiErrorCodeLibrary::find('TCSI_STUDENT_MANDATORY_001');
print_r($error->toArray());

// Test validator
$validator = new App\Services\TCSI\Validation\StudentValidator();
$student = new App\Models\Student(['chessn' => null]);
$result = $validator->validate($student, '2024_S1');
print_r($result['errors']);

// Test auto-fix
$autoFix = new App\Services\TCSI\ErrorProcessing\TcsiAutoFixService();
$result = $autoFix->attemptFix(1);
print_r($result);
```

## Error Code Maintenance

### Regular Review
- Monthly: Review error frequency
- Quarterly: Update resolution guidance
- Annually: Review auto-fix success rates

### Metrics to Track
- Most common errors
- Auto-fix success rate
- Average time to resolve
- Errors by file type

## Support

For questions about specific error codes:
- Check error_code_library table
- Review validator source code
- Contact: tcsi-support@institution.edu.au
