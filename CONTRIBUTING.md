# Contributing to TCSI Submission System

Thank you for considering contributing!

## Development Process

1. Fork the repository
2. Create a feature branch from `develop`
3. Make your changes
4. Write/update tests
5. Ensure all tests pass
6. Submit a pull request

## Coding Standards

### PHP/Laravel
- Follow PSR-12 coding standard
- Use strict types: `declare(strict_types=1);`
- Type hint everything
- Write PHPDoc blocks for complex methods

### JavaScript/Vue
- Use Vue 3 Composition API
- Component names should be PascalCase
- Props should be camelCase

### Database
- Always create migrations for schema changes
- Include rollback logic
- Add indexes for foreign keys

## Testing Requirements

- Write unit tests for all services
- Write feature tests for all workflows
- Maintain minimum 80% code coverage
```bash
php artisan test --coverage --min=80
```

## Pull Request Process

1. Update CHANGELOG.md
2. Ensure all tests pass
3. Update documentation
4. Request review from maintainers

## Questions?

Open an issue or contact the maintainers.
```

---

## File 26: LICENSE

**Path:** `LICENSE`
```
MIT License

Copyright (c) 2025 [Your Institution Name]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
