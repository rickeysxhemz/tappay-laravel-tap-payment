# Laravel Tap Payments SDK

A modern, fluent Laravel package for integrating with the Tap Payments v2 API. Supports all MENA payment methods including KNET, MADA, Benefit, and more.

## Features

- 🚀 Fluent builder pattern for creating charges
- 💳 Support for all Tap payment methods (Cards, KNET, MADA, Benefit, BNPL, etc.)
- 🔐 Secure webhook validation
- 👤 Billable trait for Eloquent models (Laravel Cashier pattern)
- ✅ Comprehensive testing support
- 📦 Laravel 11 & 12 compatible
- 🎯 Full type safety with PHP 8.2+

## Installation

Install via Composer:

```bash
composer require tapPay/laravel-tap-Payment
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=tap-config
```

## Configuration

Add your Tap API credentials to `.env`:

```env
TAP_SECRET_KEY=sk_test_your_secret_key
TAP_PUBLISHABLE_KEY=pk_test_your_publishable_key
TAP_CURRENCY=USD
```

## Usage

### Basic Charge

```php
use tapPay\Tap\Facades\Tap;

$charge = Tap::charges()->create([
    'amount' => 100.00,
    'currency' => 'USD',
    'source' => ['id' => 'src_card'],
    'customer' => [
        'first_name' => 'John',
        'email' => 'john@example.com',
    ],
    'redirect' => ['url' => 'https://your-site.com/callback'],
]);

// Redirect user to payment page
return redirect($charge->transactionUrl());
```

### Using Builder Pattern

```php
use tapPay\Tap\Facades\Tap;

$charge = Tap::charges()
    ->newBuilder()
    ->amount(100.00)
    ->currency('KWD')
    ->withKNET()  // or withMADA(), withBenefit(), etc.
    ->customer([
        'first_name' => 'John',
        'email' => 'john@example.com',
    ])
    ->redirectUrl('https://your-site.com/callback')
    ->postUrl('https://your-site.com/webhook')
    ->saveCard()
    ->create();
```

### Billable Trait (Recommended)

Add the trait to your User model:

```php
use tapPay\Tap\Concerns\Billable;

class User extends Authenticatable
{
    use Billable;

    // Your model code...
}
```

Add database column:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('tap_customer_id')->nullable()->index();
});
```

Now you can charge users directly:

```php
$user = Auth::user();

// Simple charge
$charge = $user->charge(100.00, 'KWD', [
    'source' => ['id' => 'src_kw.knet'],
    'redirect' => ['url' => route('payment.callback')],
]);

// Or use builder
$charge = $user->newCharge(100.00, 'KWD')
    ->withMADA()
    ->redirectUrl(route('payment.callback'))
    ->saveCard()
    ->create();
```

### Supported Payment Methods

```php
use tapPay\Tap\Enums\SourceObject;

// Regional methods
->withKNET()           // Kuwait
->withMADA()           // Saudi Arabia
->withBenefit()        // Bahrain
->withOmanNet()        // Oman
->withNAPS()           // Qatar

// Card payments
->withCard()           // Hosted card form
->withAllMethods()     // All enabled methods

// Saved card
->withToken($tokenId)

// Capture authorization
->captureAuthorization($authId)
```

### Authorize & Capture

```php
// Step 1: Create authorization
$auth = Tap::authorizations()->create([
    'amount' => 100.00,
    'currency' => 'USD',
    'source' => ['id' => 'src_card'],
    'customer' => ['id' => $customerId],
]);

// Step 2: Capture later
$charge = Tap::charges()->create([
    'amount' => 100.00,
    'currency' => 'USD',
    'source' => ['id' => $auth->id()],
    'customer' => ['id' => $customerId],
]);
```

### Refunds

```php
$refund = Tap::refunds()->create([
    'charge_id' => 'chg_xxxxxx',
    'amount' => 50.00,
    'currency' => 'USD',
    'reason' => 'requested_by_customer',
]);
```

### Webhooks

Webhooks are automatically registered at `/tap/webhook`.

Listen for webhook events:

```php
// In EventServiceProvider
protected $listen = [
    'tap.webhook.charge' => [
        \App\Listeners\HandleTapCharge::class,
    ],
];

// Listener
class HandleTapCharge
{
    public function handle($payload)
    {
        $chargeId = $payload['id'];
        $status = $payload['status'];

        // Update your order, etc.
    }
}
```

### Customer Management

```php
// Create customer
$customer = Tap::customers()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
]);

// Retrieve customer
$customer = Tap::customers()->retrieve('cus_xxxxx');

// Update customer
$customer = Tap::customers()->update('cus_xxxxx', [
    'first_name' => 'Jane',
]);
```

### Saved Cards

```php
// Save card during first charge
$charge = Tap::charges()->create([
    'amount' => 100.00,
    'currency' => 'USD',
    'source' => ['id' => 'src_card'],
    'customer' => ['id' => $customerId],
    'save_card' => true,
]);

$cardId = $charge->cardId();  // Returns 'card_xxxxx'

// Use saved card for future charges
$token = Tap::tokens()->create([
    'card' => $cardId,
    'customer' => $customerId,
]);

$charge = Tap::charges()->create([
    'amount' => 50.00,
    'currency' => 'USD',
    'source' => ['id' => $token->id()],
    'customer' => ['id' => $customerId],
]);
```

## Testing

```bash
composer test
```

## API Documentation

Full Tap Payments API documentation: [developers.tap.company](https://developers.tap.company)

## License

MIT License

## Credits

- Waqas Majeed
