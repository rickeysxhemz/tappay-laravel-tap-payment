# Testing Guide

This document outlines the testing strategy and implementation for the TapPay Laravel package.

## Overview

The package uses PHPUnit 11 with Orchestra Testbench for testing. All external API calls are mocked using Guzzle's MockHandler to ensure fast, reliable, and isolated tests.

## Requirements

- PHP 8.2+
- PHPUnit 11.0+
- Orchestra Testbench 10.7+
- Mockery 1.6+

## Installation

```bash
composer install
```

## Test Structure

```
tests/
├── TestCase.php                    # Base test case extending Orchestra Testbench
├── Unit/                           # Isolated unit tests
│   └── Enums/
│       └── ChargeStatusTest.php
└── Feature/                        # Integration tests with Laravel
    ├── ChargeServiceTest.php
    ├── ChargeBuilderTest.php
    ├── WebhookTest.php
    └── BillableTraitTest.php
```

## Running Tests

### All Tests
```bash
vendor/bin/phpunit
```

### Specific Test Suite
```bash
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
```

### Specific Test File
```bash
vendor/bin/phpunit tests/Feature/ChargeServiceTest.php
```

### Specific Test Method
```bash
vendor/bin/phpunit --filter test_method_name
```

### With Coverage Report
```bash
vendor/bin/phpunit --coverage-html coverage
```

## Test Categories

### Unit Tests

Unit tests verify individual components in isolation without external dependencies or Laravel framework features.

**Characteristics:**
- No database access
- No HTTP requests
- No framework dependencies
- Fast execution (< 10ms per test)

**Example:**
```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TapPay\Tap\Enums\ChargeStatus;

class ChargeStatusTest extends TestCase
{
    #[Test]
    public function it_identifies_successful_charges(): void
    {
        $this->assertTrue(ChargeStatus::CAPTURED->isSuccessful());
        $this->assertFalse(ChargeStatus::FAILED->isSuccessful());
    }
}
```

### Feature Tests

Feature tests verify integration with Laravel framework components and service interactions.

**Characteristics:**
- Uses Orchestra Testbench
- Provides Laravel service container
- Mocks external API calls
- Tests full request/response cycles

**Example:**
```php
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Tests\TestCase;

class ChargeServiceTest extends TestCase
{
    #[Test]
    public function it_creates_charge_successfully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'status' => 'INITIATED',
        ])));

        $charge = $this->chargeService->create([
            'amount' => 10.50,
            'currency' => 'USD',
        ]);

        $this->assertSame('chg_test_123', $charge->id());
    }
}
```

## Mocking External APIs

All HTTP requests are mocked using Guzzle's MockHandler to prevent actual API calls during testing.

### Implementation Pattern

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

protected function setUp(): void
{
    parent::setUp();

    // Initialize mock handler
    $this->mockHandler = new MockHandler();
    $handlerStack = HandlerStack::create($this->mockHandler);
    $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

    // Create HTTP client
    $httpClient = new Client(config('tap.secret_key'));

    // Inject mocked Guzzle client using reflection
    $reflection = new \ReflectionClass($httpClient);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($httpClient, $guzzleClient);

    $this->chargeService = new ChargeService($httpClient);
}
```

### Why Reflection is Used

The `client` property in the `Client` class is protected. Reflection allows us to inject a mocked Guzzle client without modifying production code or breaking encapsulation.

```php
$property->setAccessible(true);  // Bypasses protected visibility
$property->setValue($httpClient, $guzzleClient);  // Injects mock
```

### Mocking Responses

#### Success Response
```php
$this->mockHandler->append(new Response(200, [], json_encode([
    'id' => 'chg_123',
    'status' => 'CAPTURED',
])));
```

#### Client Error (400/422)
```php
$this->mockHandler->append(new Response(400, [], json_encode([
    'message' => 'Invalid amount',
    'errors' => ['amount' => ['Minimum amount is 0.1']],
])));
```

#### Authentication Error (401)
```php
$this->mockHandler->append(new Response(401, [], json_encode([
    'error' => 'Unauthorized',
])));

$this->expectException(AuthenticationException::class);
```

#### Server Error (500)
```php
$this->mockHandler->append(new Response(500, [], json_encode([
    'message' => 'Internal Server Error',
])));

$this->expectException(ApiErrorException::class);
```

## Testing Webhooks

### Signature Generation

Webhooks are validated using HMAC-SHA256 signatures. Tests must generate valid signatures to verify the validation logic.

```php
protected function generateSignature(array $payload): string
{
    $fields = [];

    if (isset($payload['id'])) $fields[] = $payload['id'];
    if (isset($payload['amount'])) $fields[] = $payload['amount'];
    if (isset($payload['currency'])) $fields[] = $payload['currency'];
    if (isset($payload['status'])) $fields[] = $payload['status'];
    if (isset($payload['created'])) $fields[] = $payload['created'];

    $hashString = implode('', $fields);
    return hash_hmac('sha256', $hashString, $this->secretKey);
}
```

### Webhook Testing Example

```php
#[Test]
public function it_validates_webhook_signature(): void
{
    Event::fake();

    $payload = [
        'object' => 'charge',
        'id' => 'chg_123',
        'status' => 'CAPTURED',
        'created' => time(),
    ];

    $signature = $this->generateSignature($payload);

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'HTTP_X_TAP_SIGNATURE' => $signature,
    ], json_encode($payload));

    $controller = new WebhookController($this->validator);
    $response = $controller($request);

    $this->assertEquals(200, $response->getStatusCode());
    Event::assertDispatched('tap.webhook.charge');
}
```

## Best Practices

### Test Isolation

Each test should be independent and not rely on state from other tests.

```php
protected function setUp(): void
{
    parent::setUp();
    // Initialize test state
}

protected function tearDown(): void
{
    // Clean up resources
    parent::tearDown();
}
```

### Use Realistic Test Data

Mock responses should match the structure and data types returned by the actual Tap API.

```php
// Use actual API response structure from Tap documentation
$this->mockHandler->append(new Response(200, [], json_encode([
    'id' => 'chg_TS022420241733551603Lp910296',
    'object' => 'charge',
    'amount' => 10.50,
    'currency' => 'KWD',
    'status' => 'INITIATED',
    'transaction' => [
        'url' => 'https://sandbox.payments.tap.company/redirect/...',
    ],
])));
```

### Test Both Success and Failure Paths

```php
#[Test]
public function it_handles_successful_charge_creation(): void
{
    // Test success scenario
}

#[Test]
public function it_handles_validation_errors(): void
{
    // Test failure scenario
}

#[Test]
public function it_handles_authentication_failures(): void
{
    // Test authentication errors
}
```

### Use Data Providers for Multiple Scenarios

```php
#[Test]
#[DataProvider('paymentMethodProvider')]
public function it_supports_payment_method(string $method, string $expected): void
{
    $builder = new ChargeBuilder($this->chargeService);
    $data = $builder->amount(10)->source($method)->toArray();

    $this->assertSame($expected, $data['source']['id']);
}

public static function paymentMethodProvider(): array
{
    return [
        ['src_card', 'src_card'],
        ['src_kw.knet', 'src_kw.knet'],
        ['src_sa.mada', 'src_sa.mada'],
    ];
}
```

### Avoid Real API Calls

Never make actual HTTP requests in tests. Always use mocked responses.

```php
// Never do this in tests
$charge = (new TapClient($apiKey))->createCharge($data);

// Always do this
$this->mockHandler->append(new Response(200, [], $mockData));
$charge = $this->chargeService->create($data);
```

### Use Appropriate Assertions

```php
// Identity checks
$this->assertSame('expected', $actual);

// Type checks
$this->assertInstanceOf(Charge::class, $charge);

// Boolean checks
$this->assertTrue($condition);
$this->assertFalse($condition);

// Null checks
$this->assertNull($value);
$this->assertNotNull($value);

// Array checks
$this->assertArrayHasKey('key', $array);
$this->assertCount(5, $array);
```

## PHPUnit 11 Syntax

This package uses PHPUnit 11 attributes instead of docblock annotations.

**Modern syntax (used):**
```php
use PHPUnit\Framework\Attributes\Test;

#[Test]
public function it_performs_action(): void
{
    // Test implementation
}
```

**Legacy syntax (not used):**
```php
/** @test */
public function it_performs_action(): void
{
    // Test implementation
}
```

## Coverage Goals

Recommended minimum coverage targets:

| Component | Target | Priority |
|-----------|--------|----------|
| Services | 90% | High |
| Builders | 85% | High |
| Webhooks | 95% | Critical |
| Resources | 70% | Medium |
| Enums | 100% | Low |

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: ['8.2', '8.3']

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: mbstring, xml, ctype, json
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Tests
        run: vendor/bin/phpunit --coverage-text
```

## Common Issues

### MockHandler Queue Empty

**Problem:** Test fails with "Mock queue is empty"

**Solution:** Ensure you append a response for each HTTP request made in the test.

```php
// If test makes 2 requests, append 2 responses
$this->mockHandler->append(new Response(200, [], $data1));
$this->mockHandler->append(new Response(200, [], $data2));
```

### Reflection Property Not Found

**Problem:** `ReflectionException: Property does not exist`

**Solution:** Verify the property name matches exactly (case-sensitive).

```php
$property = $reflection->getProperty('client'); // Must match class property name
```

### Facade Not Resolved

**Problem:** Facade returns null in tests

**Solution:** Ensure service provider is registered in TestCase.

```php
protected function getPackageProviders($app): array
{
    return [TapServiceProvider::class];
}
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Orchestra Testbench](https://packages.tools/testbench)
- [Guzzle Testing](https://docs.guzzlephp.org/en/stable/testing.html)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Tap Payments API Documentation](https://developers.tap.company/)

## Support

For issues related to:
- **Package tests:** Open an issue on GitHub
- **PHPUnit:** Consult PHPUnit documentation
- **Orchestra Testbench:** See Testbench documentation
- **Tap API:** Contact Tap Payments support