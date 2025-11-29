# Webhooks

## Overview

Webhooks allow Tap to notify your application when events occur. The package automatically handles:

- Signature validation (HMAC-SHA256)
- Replay attack prevention
- Event dispatching

## Default Routes

The package registers these routes automatically:

| Route | Method | Purpose |
|-------|--------|---------|
| `/tap/webhook` | POST | Webhook endpoint |
| `/tap/callback` | GET | Payment redirect callback |

**Note:** These routes are registered outside the `web` middleware group, so CSRF verification is not applied.

## Webhook Security

### Signature Validation

All incoming webhooks are validated using HMAC-SHA256:

```php
// Validation happens automatically
// Uses TAP_WEBHOOK_SECRET or TAP_SECRET
```

### Replay Attack Prevention

Webhooks older than 5 minutes are rejected by default:

```env
TAP_WEBHOOK_TOLERANCE=300  # seconds
```

## Listening to Webhooks

### Option 1: Event Classes (Recommended)

```php
// In EventServiceProvider
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;

protected $listen = [
    WebhookReceived::class => [
        \App\Listeners\HandleTapWebhook::class,
    ],
    WebhookValidationFailed::class => [
        \App\Listeners\LogFailedWebhook::class,
    ],
];
```

```php
// App\Listeners\HandleTapWebhook.php
use TapPay\Tap\Events\WebhookReceived;

class HandleTapWebhook
{
    public function handle(WebhookReceived $event): void
    {
        // Check event type
        if ($event->isType('charge')) {
            $this->handleCharge($event);
        } elseif ($event->isType('refund')) {
            $this->handleRefund($event);
        }
    }

    private function handleCharge(WebhookReceived $event): void
    {
        $chargeId = $event->getId();
        $status = $event->payload['status'];

        if ($status === 'CAPTURED') {
            // Payment successful - update order
            Order::where('charge_id', $chargeId)
                ->update(['status' => 'paid']);
        }
    }
}
```

### Option 2: String-based Events

```php
// In EventServiceProvider
protected $listen = [
    'tap.webhook.charge' => [
        \App\Listeners\HandleTapCharge::class,
    ],
    'tap.webhook.refund' => [
        \App\Listeners\HandleTapRefund::class,
    ],
];
```

```php
// App\Listeners\HandleTapCharge.php
class HandleTapCharge
{
    public function handle(array $payload): void
    {
        $chargeId = $payload['id'];
        $status = $payload['status'];

        // Process charge...
    }
}
```

## Available Events

### Event Classes

| Event | Description |
|-------|-------------|
| `WebhookReceived` | Any valid webhook received |
| `WebhookValidationFailed` | Signature validation failed |
| `WebhookProcessingFailed` | Error during processing |

### String Events

| Event | Description |
|-------|-------------|
| `tap.webhook.charge` | Charge webhook |
| `tap.webhook.refund` | Refund webhook |
| `tap.webhook.customer` | Customer webhook |
| `tap.webhook.authorize` | Authorization webhook |
| `tap.webhook.token` | Token webhook |
| `tap.webhook.received` | Catch-all event |

## WebhookReceived Event Methods

```php
$event->isType('charge');      // Check resource type
$event->getId();               // Get resource ID
$event->payload;               // Full payload array
$event->resource;              // Resource type string
```

## Error Handling

```php
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Events\WebhookProcessingFailed;

// Log validation failures
Event::listen(WebhookValidationFailed::class, function ($event) {
    Log::warning('Webhook validation failed', [
        'reason' => $event->reason,
        'ip' => $event->ip,
        'context' => $event->context,
    ]);
});

// Handle processing errors
Event::listen(WebhookProcessingFailed::class, function ($event) {
    Log::error('Webhook processing failed', [
        'error' => $event->getErrorMessage(),
        'resource' => $event->resource,
    ]);
});
```

## Custom Route Path

Change the route prefix via environment:

```env
TAP_PATH=payment
```

Routes become:
- `/payment/webhook`
- `/payment/callback`

## Manual Route Registration

If you need full control, disable automatic routes:

```php
// In AppServiceProvider
use TapPay\Tap\Tap;

public function register(): void
{
    Tap::ignoreRoutes();
}
```

Then register your own routes:

```php
// routes/web.php
use TapPay\Tap\Http\Controllers\WebhookController;

Route::post('my-webhook', [WebhookController::class, 'handleWebhook'])
    ->withoutMiddleware(['web', 'csrf']);
```

**Important:** When defining webhook routes manually, exclude them from CSRF verification.

### Laravel 11+

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'my-webhook',
    ]);
})
```

### Laravel 10 and earlier

```php
// App\Http\Middleware\VerifyCsrfToken.php
protected $except = [
    'my-webhook',
];
```

## Testing Webhooks

### Using Tap Dashboard

1. Go to Tap Dashboard > Webhooks
2. Add your webhook URL: `https://yourdomain.com/tap/webhook`
3. Select events to receive

### Using ngrok for Local Development

```bash
ngrok http 8000
```

Use the ngrok URL in Tap Dashboard for testing.

### Unit Testing

```php
use TapPay\Tap\Events\WebhookReceived;

public function test_webhook_updates_order()
{
    Event::fake();

    $order = Order::factory()->create(['charge_id' => 'chg_test']);

    // Simulate webhook
    event(new WebhookReceived([
        'id' => 'chg_test',
        'object' => 'charge',
        'status' => 'CAPTURED',
    ]));

    Event::assertDispatched(WebhookReceived::class);
}
```