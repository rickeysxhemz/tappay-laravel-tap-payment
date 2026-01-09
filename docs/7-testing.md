# Testing

## Test Commands

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run feature tests
composer test:feature

# Run with coverage
composer test:coverage

# Run in parallel
composer test:parallel
```

## Test Structure

```
tests/
├── Unit/           # Fast, isolated unit tests
├── Feature/        # Integration tests with mocked API
├── Fixtures/       # Test data and API responses
├── Pest.php        # Pest configuration
└── TestCase.php    # Base test case
```

## Testing Your Application

### Mocking the Tap Facade

```php
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;

public function test_checkout_creates_charge()
{
    // Mock the charges service
    Tap::shouldReceive('charges->create')
        ->once()
        ->andReturn(new Charge([
            'id' => 'chg_test_123',
            'status' => 'INITIATED',
            'transaction' => [
                'url' => 'https://tap.company/pay/123',
            ],
        ]));

    $response = $this->post('/checkout', [
        'amount' => 100,
    ]);

    $response->assertRedirect('https://tap.company/pay/123');
}
```

### Testing Webhooks

```php
use TapPay\Tap\Events\WebhookReceived;

public function test_webhook_updates_order()
{
    Event::fake();

    $order = Order::factory()->create([
        'charge_id' => 'chg_test_123',
        'status' => 'pending',
    ]);

    // Simulate webhook
    $payload = [
        'id' => 'chg_test_123',
        'object' => 'charge',
        'status' => 'CAPTURED',
    ];

    $this->postJson('/tap/webhook', $payload, [
        'hashstring' => $this->generateSignature($payload),
    ])->assertOk();

    Event::assertDispatched(WebhookReceived::class);

    $this->assertEquals('paid', $order->fresh()->status);
}

private function generateSignature(array $payload): string
{
    $hashString = 'x_id' . ($payload['id'] ?? '')
                . 'x_amount' . ($payload['amount'] ?? '')
                . 'x_currency' . ($payload['currency'] ?? '')
                . 'x_gateway_reference' . ($payload['gateway']['reference'] ?? $payload['reference']['gateway'] ?? '')
                . 'x_payment_reference' . ($payload['reference']['payment'] ?? '')
                . 'x_status' . ($payload['status'] ?? '')
                . 'x_created' . ($payload['created'] ?? '');

    return hash_hmac('sha256', $hashString, config('tap.webhook.secret'));
}
```

### Testing Billable Trait

```php
public function test_user_can_be_charged()
{
    $user = User::factory()->create();

    Tap::shouldReceive('charges->create')
        ->once()
        ->andReturn(new Charge([
            'id' => 'chg_test',
            'status' => 'INITIATED',
            'transaction' => ['url' => 'https://pay.tap.company/123'],
        ]));

    $charge = $user->charge(10000, 'SAR', [
        'source' => ['id' => 'src_card'],
        'redirect' => ['url' => 'https://example.com/callback'],
    ]);

    $this->assertEquals('chg_test', $charge->id());
}
```

### Testing Customer Creation

```php
public function test_customer_is_created_on_first_charge()
{
    $user = User::factory()->create([
        'tap_customer_id' => null,
    ]);

    Tap::shouldReceive('customers->create')
        ->once()
        ->andReturn(new Customer(['id' => 'cus_test_123']));

    Tap::shouldReceive('charges->create')
        ->once()
        ->andReturn(new Charge([
            'id' => 'chg_test',
            'status' => 'INITIATED',
        ]));

    $user->charge(10000, 'SAR', [...]);

    $this->assertEquals('cus_test_123', $user->fresh()->tap_customer_id);
}
```

## Test Cards

Use these test card numbers in sandbox:

| Card Number | Result |
|-------------|--------|
| 4111 1111 1111 1111 | Success |
| 4000 0000 0000 0002 | Declined |
| 4000 0000 0000 9995 | Insufficient funds |
| 4000 0000 0000 0069 | Expired card |

**Common test values:**
- CVV: `123`
- Expiry: Any future date

## Sandbox vs Production

### Using Sandbox

```env
TAP_KEY=pk_test_xxxxx
TAP_SECRET=sk_test_xxxxx
```

### Using Production

```env
TAP_KEY=pk_live_xxxxx
TAP_SECRET=sk_live_xxxxx
```

No code changes needed - just environment variables.

## Real API Testing

To run tests against the real Tap API:

```xml
<!-- phpunit.xml -->
<env name="TAP_REAL_API_TESTING" value="true"/>
<env name="TAP_SECRET" value="sk_test_YOUR_KEY"/>
```

```bash
composer test:integration
```

**Warning**: This makes real API calls. Use test keys only.

## Debugging

### Log API Requests

```php
// In a test or debug scenario
\TapPay\Tap\Http\Client::enableLogging();

$charge = Tap::charges()->create([...]);

// Check logs for request/response details
```

### Inspect Charge Response

```php
$charge = Tap::charges()->retrieve('chg_xxx');

dd([
    'id' => $charge->id(),
    'status' => $charge->status(),
    'amount' => $charge->amount(),
    'raw' => $charge->toArray(),
]);
```