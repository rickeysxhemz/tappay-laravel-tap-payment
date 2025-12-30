<h1 align="center">Laravel Tap Payments</h1>

<p align="center">
A fluent Laravel integration for Tap Payments API
</p>

<p align="center">
<a href="https://github.com/rickeysxhemz/tappay-laravel-tap-payment/actions"><img src="https://github.com/rickeysxhemz/tappay-laravel-tap-payment/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/dt/tappay/laravel-tap-payment" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/v/tappay/laravel-tap-payment" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/l/tappay/laravel-tap-payment" alt="License"></a>
</p>

## Introduction

Laravel Tap Payments provides an expressive, fluent interface to [Tap Payments](https://www.tap.company/) billing services. It handles almost all of the boilerplate payment processing code you are dreading writing. In addition to basic charge management, the package can handle saved cards, webhooks, refunds, authorizations, and more.

## Official Documentation

Documentation for the package can be found in the [docs](docs/) folder:

- [Installation](docs/1-installation.md)
- [Configuration](docs/2-configuration.md)
- [Charges](docs/3-charges.md)
- [Webhooks](docs/4-webhooks.md)
- [Saved Cards](docs/5-saved-cards.md)
- [Billable Trait](docs/6-billable.md)
- [Testing](docs/7-testing.md)
- [Marketplace](docs/8-marketplace.md)

## Quick Start

Install the package via Composer:

```bash
composer require tappay/laravel-tap-payment
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=tap-config
```

Add your credentials to `.env`:

```env
TAP_KEY=pk_test_your_publishable_key
TAP_SECRET=sk_test_your_secret_key
TAP_CURRENCY=SAR
```

### Basic Usage

```php
use TapPay\Tap\Facades\Tap;

// Create a charge
$charge = Tap::charges()->newBuilder()
    ->amount(100.00)              // Amount in currency units (100.00 SAR)
    ->currency('SAR')
    ->withCard()
    ->customer([
        'first_name' => 'John',
        'email' => 'john@example.com',
    ])
    ->redirectUrl('https://example.com/callback')
    ->create();

// Redirect to payment page
return redirect($charge->transactionUrl());
```

### Billable Model

Add the `Billable` trait to your User model:

```php
use TapPay\Tap\Concerns\Billable;
use TapPay\Tap\Contracts\Billable as BillableContract;

class User extends Authenticatable implements BillableContract
{
    use Billable;
}
```

Now charge users directly:

```php
$user->charge(10000, 'SAR', [
    'source' => ['id' => 'src_card'],
    'redirect' => ['url' => route('payment.callback')],
]);
```

### Available Services

| Service | Description |
|---------|-------------|
| `Tap::charges()` | Create, retrieve, list, and bulk export charges |
| `Tap::customers()` | Full CRUD operations for customers |
| `Tap::refunds()` | Process, manage, and bulk export refunds |
| `Tap::authorizations()` | Authorization, capture, void, and bulk export |
| `Tap::tokens()` | Create and manage payment tokens |
| `Tap::cards()` | Manage saved cards for customers |
| `Tap::invoices()` | Create, finalize, remind, and cancel invoices |
| `Tap::subscriptions()` | Handle recurring subscriptions |
| `Tap::merchants()` | Marketplace sub-merchant management |
| `Tap::destinations()` | Payment split destinations |
| `Tap::payouts()` | Track merchant settlements |

### Supported Payment Methods

| Region | Methods |
|--------|---------|
| Global | Card, All Methods |
| Kuwait | KNET, KFAST |
| Saudi Arabia | Mada, STC Pay |
| Bahrain | Benefit |
| Oman | OmanNet |
| Qatar | NAPS |
| Egypt | Fawry |
| BNPL | Tabby, Deema |

### Marketplace & Payment Splits

Split payments across multiple merchants:

```php
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\ValueObjects\Destination;

// Create a charge with payment splits
$charge = Tap::charges()->newBuilder()
    ->amount(100.00)
    ->withCard()
    ->destinations([
        Destination::make('merchant_123', 70.00),  // 70% to vendor
        Destination::make('merchant_456', 30.00),  // 30% platform fee
    ])
    ->redirectUrl('https://example.com/callback')
    ->create();

// Manage sub-merchants
$merchant = Tap::merchants()->create([
    'name' => 'Vendor Store',
    'email' => 'vendor@example.com',
    'country_code' => 'SA',
]);

// Track payouts
$payouts = Tap::payouts()->listByMerchant('merchant_123');
```

### Authorizations (Two-Step Payments)

Hold funds without capturing immediately:

```php
use TapPay\Tap\Facades\Tap;

// Create authorization with auto-capture after 24 hours
$auth = Tap::authorizations()->newBuilder()
    ->amount(5000)
    ->currency('SAR')
    ->source('src_card')
    ->autoCapture(24)                    // Auto-capture after 24 hours
    ->idempotent($order->id)             // Prevent duplicate authorizations
    ->redirectUrl('https://example.com/callback')
    ->create();

// Or auto-void if not captured
$auth = Tap::authorizations()->newBuilder()
    ->amount(5000)
    ->source('src_card')
    ->autoVoid(48)                       // Auto-void after 48 hours
    ->create();

// Manually void an authorization
Tap::authorizations()->void('auth_xxxxx');

// Bulk export authorizations
Tap::authorizations()->download(['status' => 'AUTHORIZED']);
```

### Invoices

Create and manage payment invoices:

```php
use TapPay\Tap\Facades\Tap;

// Create an invoice
$invoice = Tap::invoices()->create([
    'amount' => 100.00,
    'currency' => 'SAR',
    'customer' => ['id' => 'cus_xxxxx'],
]);

// Finalize a draft invoice
Tap::invoices()->finalize('inv_xxxxx');

// Send payment reminder
Tap::invoices()->remind('inv_xxxxx');

// Cancel an invoice
Tap::invoices()->cancel('inv_xxxxx');
```

### Idempotency

Prevent duplicate charges with idempotent keys:

```php
$charge = Tap::charges()->newBuilder()
    ->amount(10000)
    ->withCard()
    ->idempotent($order->id)             // Same key = same response within 24h
    ->redirectUrl($callbackUrl)
    ->create();
```

Works with charges, authorizations, and refunds. See [Charges documentation](docs/3-charges.md#idempotency-preventing-double-charges) for details.

### Events

The package dispatches events you can listen to:

| Event | Description |
|-------|-------------|
| `PaymentSucceeded` | Dispatched when a payment is successful |
| `PaymentFailed` | Dispatched when a payment fails |
| `PaymentRetrievalFailed` | Dispatched when charge retrieval fails (API errors) |
| `WebhookReceived` | Dispatched when a valid webhook is received |
| `WebhookValidationFailed` | Dispatched when webhook validation fails |
| `WebhookProcessingFailed` | Dispatched when webhook processing throws an exception |

Example listener:

```php
use TapPay\Tap\Events\PaymentSucceeded;
use TapPay\Tap\Events\PaymentFailed;

// In EventServiceProvider
protected $listen = [
    PaymentSucceeded::class => [
        SendPaymentConfirmation::class,
    ],
    PaymentFailed::class => [
        NotifyPaymentFailure::class,
    ],
];
```

### Webhooks

Configure webhook handling in your `config/tap.php`:

```php
'webhook' => [
    'secret' => env('TAP_WEBHOOK_SECRET'),
    'tolerance' => 300, // 5 minutes
    'allowed_resources' => ['charge', 'refund', 'customer'],
],
```

The package automatically:
- Validates webhook signatures using HMAC-SHA256
- Prevents replay attacks with timestamp tolerance
- Dispatches events for each webhook type

### Security Features

- HMAC-SHA256 webhook signature validation
- Timing-safe signature comparison
- Open redirect protection on callbacks
- Input validation on all builder methods
- Sensitive parameter protection for API keys

## Testing

```bash
# Run tests
composer test

# Run static analysis
composer analyse

# Run code style checks
composer lint
```

## Contributing

Thank you for considering contributing to Laravel Tap Payments! The contribution guide can be found in the [CONTRIBUTING.md](docs/CONTRIBUTING.md) file.

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](docs/CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please review [our security policy](docs/SECURITY.md) on how to report security vulnerabilities.

## License

Laravel Tap Payments is open-sourced software licensed under the [MIT license](LICENSE).