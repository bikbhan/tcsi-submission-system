# TCSI System - Developer Guide

## Development Environment Setup

### Required Tools
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+
- Git
- IDE (VS Code, PHPStorm)

### Recommended VS Code Extensions
- PHP Intelephense
- Laravel Extension Pack
- Vetur (Vue)
- ESLint
- GitLens

## Project Architecture

### Backend (Laravel)

#### Layers
1. **Models** - Database entities
2. **Services** - Business logic
3. **Validators** - Data validation
4. **Controllers** - HTTP endpoints
5. **Migrations** - Database schema

#### Service Layer Pattern
```php
// Services contain business logic
class TcsiValidationService
{
    public function validateStudent($student, $period)
    {
        $validator = new StudentValidator();
        return $validator->validate($student, $period);
    }
}

// Controllers are thin - just handle HTTP
class TcsiSubmissionController
{
    public function validate(Request $request)
    {
        $result = $this->validationService->validateStudent(...);
        return response()->json($result);
    }
}
```

### Frontend (Vue 3 + Inertia)

#### Component Structure
```
Pages/            # Full page components
  TCSI/
    Configuration/
      Index.vue   # Main config page
    Submission/
      Wizard.vue  # Submission wizard

Components/       # Reusable components
  TCSI/
    ErrorCard.vue
    StatusBadge.vue
```

## Coding Standards

### PHP (PSR-12)

**Good:**
```php
<?php

declare(strict_types=1);

namespace App\Services\TCSI\Validation;

class StudentValidator extends BaseValidator
{
    private const VALID_GENDERS = ['M', 'F', 'X'];
    
    public function validate($student, string $reportingPeriod): array
    {
        // Clear, type-hinted code
    }
}
```

**Bad:**
```php
<?php
class validator {
    function validate($s) {
        // No types, unclear naming
    }
}
```

### Database

**Migrations - Always Reversible**
```php
public function up(): void
{
    Schema::create('table_name', function (Blueprint $table) {
        // Create table
    });
}

public function down(): void
{
    Schema::dropIfExists('table_name');
}
```

### Vue 3 Composition API

**Good:**
```vue
<script setup>
import { ref, computed } from 'vue';

const errors = ref([]);
const errorCount = computed(() => errors.value.length);
</script>
```

**Bad:**
```vue
<script>
export default {
  data() {
    return { errors: [] }
  }
}
</script>
```

## Working with Validators

### Creating a New Validator

1. **Extend BaseValidator**
```php
class MyValidator extends BaseValidator
{
    public function validate($record, string $reportingPeriod): array
    {
        $this->reset();
        $this->currentRecord = $record;
        $this->reportingPeriod = $reportingPeriod;
        
        $recordId = $this->getRecordIdentifier();
        
        $this->validateMandatoryFields($recordId);
        $this->validateFormats($recordId);
        $this->validateBusinessRules($recordId);
        
        return $this->getValidationResult();
    }
}
```

2. **Use Helper Methods**
```php
// Mandatory field
$this->validateMandatory('field_name', 'ERROR_CODE', $recordId);

// Date format
$this->validateDateFormat('date_field', 'ERROR_CODE', $recordId);

// Valid values list
$this->validateInList('field', ['A', 'B', 'C'], 'ERROR_CODE', $recordId);

// Numeric range
$this->validateNumeric('field', 'ERROR_CODE', 0, 100, $recordId);
```

3. **Add Custom Validations**
```php
private function validateCustomRule(string $recordId): void
{
    if ($this->currentRecord->field1 > $this->currentRecord->field2) {
        $this->addError(
            'ERROR_CODE',
            'field1',
            $this->currentRecord->field1,
            $recordId
        );
    }
}
```

## Testing

### Unit Tests
```php
namespace Tests\Unit\TCSI;

use Tests\TestCase;
use App\Services\TCSI\Validation\StudentValidator;

class StudentValidatorTest extends TestCase
{
    public function test_validates_mandatory_chessn()
    {
        $student = new Student(['chessn' => null]);
        $validator = new StudentValidator();
        
        $result = $validator->validate($student, '2024_S1');
        
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('CHESSN', $result['errors'][0]['error_message']);
    }
}
```

### Running Tests
```bash
# All tests
php artisan test

# Specific test
php artisan test --filter StudentValidatorTest

# With coverage
php artisan test --coverage
```

## Database Queries

### Eloquent Best Practices

**Good - Use Query Builder:**
```php
$students = Student::where('citizenship_status', 'A')
    ->whereYear('commencement_date', 2024)
    ->with('course')
    ->get();
```

**Bad - N+1 Problem:**
```php
$students = Student::all();
foreach ($students as $student) {
    echo $student->course->name; // N+1 queries!
}
```

**Good - Eager Loading:**
```php
$students = Student::with('course')->get();
foreach ($students as $student) {
    echo $student->course->name; // Single query
}
```

## Error Handling

### Service Layer
```php
try {
    $result = $this->validator->validate($student, $period);
    
    if ($result['has_errors']) {
        return [
            'success' => false,
            'errors' => $result['errors']
        ];
    }
    
    return ['success' => true, 'data' => $result];
    
} catch (\Exception $e) {
    Log::error('Validation failed', [
        'student_id' => $student->id,
        'error' => $e->getMessage()
    ]);
    
    throw new ValidationException('Validation failed');
}
```

### Controller Layer
```php
public function validate(Request $request)
{
    try {
        $result = $this->service->validate(...);
        return response()->json($result);
        
    } catch (ValidationException $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Unexpected error', ['exception' => $e]);
        return response()->json([
            'error' => 'An unexpected error occurred'
        ], 500);
    }
}
```

## Git Workflow

### Branch Strategy
```
main          - Production ready
develop       - Integration branch
feature/*     - New features
bugfix/*      - Bug fixes
hotfix/*      - Urgent production fixes
```

### Commit Messages
```bash
# Good
git commit -m "Add student validator with 50+ rules"
git commit -m "Fix: CHESSN padding in auto-fix service"
git commit -m "Docs: Update installation guide"

# Bad
git commit -m "update"
git commit -m "fix bug"
git commit -m "wip"
```

### Daily Workflow
```bash
# Start of day
git checkout develop
git pull origin develop
git checkout -b feature/my-feature

# During development
git add .
git commit -m "Descriptive message"

# End of day
git push origin feature/my-feature

# Create pull request on GitHub
```

## Performance Tips

### Database
1. Add indexes to foreign keys
2. Use eager loading
3. Limit result sets
4. Use chunking for large datasets
```php
// Good - Process in chunks
Student::where('year', 2024)
    ->chunk(100, function ($students) {
        foreach ($students as $student) {
            $this->process($student);
        }
    });

// Bad - Load all into memory
$students = Student::where('year', 2024)->get();
```

### Caching
```php
// Cache error library
$errorLibrary = Cache::remember('tcsi_error_library', 3600, function () {
    return TcsiErrorCodeLibrary::all()->keyBy('error_code')->toArray();
});
```

## Debugging

### Laravel Tinker
```bash
php artisan tinker
```
```php
// Test validator
$validator = new App\Services\TCSI\Validation\StudentValidator();
$student = App\Models\Student::first();
$result = $validator->validate($student, '2024_S1');
dd($result);

// Check database
App\Models\TCSI\TcsiErrorCodeLibrary::where('file_type', 'STUDENT')->count();

// Test auto-fix
$autoFix = new App\Services\TCSI\ErrorProcessing\TcsiAutoFixService();
$result = $autoFix->attemptFix(1);
dd($result);
```

### Laravel Telescope (Recommended)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Visit: http://localhost:8000/telescope

## Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure queue driver (Redis)
- [ ] Set up file storage (S3)
- [ ] Enable SSL/HTTPS
- [ ] Configure backups
- [ ] Set up monitoring
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`

### Deployment Commands
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Build assets
npm run build
```

## Resources

### Laravel
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)

### Vue 3
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js](https://inertiajs.com/)

### TCSI
- [TCSI Documentation](https://www.education.gov.au/tcsi)
- [TCSI Data Specifications](https://www.education.gov.au/tcsi)

## Support

- 💬 Ask questions in team Slack
- 📖 Check `/docs` directory
- 🐛 Report bugs via GitHub Issues
- 📧 Email: dev-team@institution.edu.au
