# Contributing to Tap Payments Laravel Package

Thank you for considering contributing to the Tap Payments Laravel package! This document provides guidelines for contributing to the project.

## Code of Conduct

This project follows Laravel's Code of Conduct. Please be respectful and constructive in all interactions.

## How to Contribute

### Reporting Bugs

- Use the GitHub issue tracker
- Include PHP version, Laravel version, and package version
- Provide steps to reproduce the issue
- Include relevant error messages and stack traces

### Suggesting Features

- Open a GitHub issue with the `enhancement` label
- Clearly describe the feature and its use case
- Explain how it aligns with the package's goals

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write tests for your changes
5. Ensure all tests pass
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## Development Setup

### Requirements

- PHP 8.2 or higher
- Composer
- Laravel 11+ or 12+

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/laravel-tap-payment.git
cd laravel-tap-payment

# Install dependencies
composer install

# Copy phpunit.xml and configure test credentials
cp phpunit.xml.dist phpunit.xml
```

### Configuration

Edit `phpunit.xml` and add your Tap test credentials:

```xml
<env name="TAP_SECRET_KEY" value="sk_test_YOUR_TEST_KEY"/>
<env name="TAP_PUBLISHABLE_KEY" value="pk_test_YOUR_TEST_KEY"/>
```

## Testing

This package follows industry-standard testing practices with comprehensive test coverage.

### Test Structure

```
tests/
├── Feature/              # Integration tests (with mocked API)
├── Unit/                 # Unit tests (no API calls)
├── Fixtures/             # Test data fixtures
├── Pest.php             # Pest configuration
└── TestCase.php         # Base test class
```

### Running Tests

```bash
# Run all tests
composer test

# Run only unit tests (fast)
composer test:unit

# Run only feature tests
composer test:feature

# Run integration tests (requires real API keys)
composer test:integration

# Run tests with coverage
composer test:coverage

# Run tests in parallel
composer test:parallel
```

### Writing Tests

We use [Pest PHP](https://pestphp.com/) for testing, which provides a beautiful and expressive testing API.

#### Unit Test Example

```php
<?php

test('can set amount', function () {
    $builder = new ChargeBuilder($service);
    $builder->amount(100.50);

    expect($builder->get('amount'))->toBe(100.50);
})->group('unit');
```

#### Feature Test Example

```php
<?php

test('can create a charge', function () {
    Http::fake([
        'api.tap.company/v2/charges' => Http::response(loadFixture('charge.json'), 200),
    ]);

    $charge = Tap::charges()->create($this->createTestChargeData());

    expect($charge)->toBeInstanceOf(Charge::class)
        ->and($charge->id())->toBeValidTapId('chg');
})->group('feature');
```

#### Integration Test Example

```php
<?php

test('can create charge with real API', function () {
    $this->requiresRealApi();

    $charge = Tap::charges()->create($this->createTestChargeData());

    expect($charge->id())->toBeValidTapId('chg')
        ->and($charge->transactionUrl())->not->toBeNull();
})->group('integration');
```

### Test Groups

- `unit` - Fast tests with no external dependencies
- `feature` - Integration tests with mocked API responses
- `integration` - Tests against real Tap API (slower, requires API keys)

### Testing Best Practices

1. **Write tests first** - Follow TDD when adding new features
2. **Mock external APIs** - Use mocked responses for feature tests
3. **Test edge cases** - Include error scenarios and boundary conditions
4. **Use fixtures** - Store common API responses in `tests/Fixtures/`
5. **Group tests properly** - Use appropriate test groups
6. **Keep tests isolated** - Each test should be independent
7. **Test exceptions** - Verify error handling
8. **Use descriptive names** - Test names should clearly describe what they test

### Coverage Requirements

- Minimum coverage: 70%
- All new features must include tests
- Bug fixes should include regression tests

## Code Style

This package follows PSR-12 coding standards.

```bash
# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style
vendor/bin/php-cs-fixer fix
```

## Static Analysis

We use PHPStan for static analysis:

```bash
vendor/bin/phpstan analyse
```

## Continuous Integration

All pull requests are automatically tested using GitHub Actions:

- Tests on PHP 8.2 and 8.3
- Tests on Laravel 11 and 12
- Tests with `prefer-lowest` and `prefer-stable` dependencies
- Code coverage reporting
- Integration tests (scheduled daily)

## Documentation

- Update README.md for user-facing changes
- Add docblocks to all public methods
- Include `@throws` tags for exceptions
- Update CHANGELOG.md following [Keep a Changelog](https://keepachangelog.com/)

## Commit Messages

- Use clear and descriptive commit messages
- Follow [Conventional Commits](https://www.conventionalcommits.org/)
- Examples:
  - `feat: add support for Apple Pay tokens`
  - `fix: handle empty webhook payloads`
  - `test: add unit tests for ChargeBuilder`
  - `docs: update installation instructions`

## Release Process

1. Update version in relevant files
2. Update CHANGELOG.md
3. Create a Git tag
4. Push tag to trigger release workflow

## Questions?

Feel free to open an issue or contact the maintainer at **dev@waqasmajeed.dev** for any questions about contributing.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.