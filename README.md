<h1 align="center">Laravel Tap Payments</h1>

<p align="center">
A fluent Laravel integration for Tap Payments API
</p>

<p align="center">
<a href="https://github.com/rickeysxhemz/laravel-tap-payment/actions"><img src="https://github.com/rickeysxhemz/laravel-tap-payment/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/dt/tappay/laravel-tap-payment" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/v/tappay/laravel-tap-payment" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/tappay/laravel-tap-payment"><img src="https://img.shields.io/packagist/l/tappay/laravel-tap-payment" alt="License"></a>
</p>

## Introduction

Laravel Tap Payments provides an expressive, fluent interface to [Tap Payments](https://www.tap.company/) billing services. It handles almost all of the boilerplate payment processing code you are dreading writing. In addition to basic charge management, the package can handle saved cards, webhooks, refunds, authorizations, and more.

## Official Documentation

Documentation for the package can be found on the [GitHub Wiki](https://github.com/rickeysxhemz/laravel-tap-payment/wiki).

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
$charge = Tap::charges()
    ->amount(10000)           // Amount in smallest currency unit (100.00 SAR)
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
$charge = Tap::charges()
    ->amount(10000)
    ->withCard()
    ->destinations([
        Destination::make('merchant_123', 7000),  // 70% to vendor
        Destination::make('merchant_456', 3000),  // 30% platform fee
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

## Contributing

Thank you for considering contributing to Laravel Tap Payments! The contribution guide can be found in the [CONTRIBUTING.md](.github/CONTRIBUTING.md) file.

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](.github/CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## License

Laravel Tap Payments is open-sourced software licensed under the [MIT license](LICENSE).