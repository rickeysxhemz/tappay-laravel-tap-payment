# Laravel Tap Payments Documentation

Welcome to the Laravel Tap Payments documentation. This package provides a fluent, expressive interface to integrate [Tap Payments](https://www.tap.company/) with your Laravel application.

## Table of Contents

1. [Installation](1-installation.md)
2. [Configuration](2-configuration.md)
3. [Charges](3-charges.md)
4. [Webhooks](4-webhooks.md)
5. [Saved Cards](5-saved-cards.md)
6. [Billable Trait](6-billable.md)
7. [Testing](7-testing.md)

## Quick Links

- [GitHub Repository](https://github.com/rickeysxhemz/laravel-tap-payment)
- [Tap Payments API Docs](https://developers.tap.company/)
- [Report Issues](https://github.com/rickeysxhemz/laravel-tap-payment/issues)

## Features

- Fluent builder pattern for creating charges
- Support for all Tap payment methods (Cards, KNET, MADA, Benefit, BNPL, etc.)
- Secure webhook validation with event-driven architecture
- Billable trait for Eloquent models (Laravel Cashier pattern)
- Saved card support for one-click payments
- Full type safety with PHP 8.2+
- Laravel Octane compatible

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x

## Quick Start

```bash
composer require tappay/laravel-tap-payment
php artisan vendor:publish --tag=tap-config
```

```env
TAP_KEY=pk_test_your_publishable_key
TAP_SECRET=sk_test_your_secret_key
TAP_CURRENCY=SAR
```

```php
use TapPay\Tap\Facades\Tap;

$charge = Tap::charges()
    ->amount(10000)
    ->currency('SAR')
    ->withCard()
    ->customer(['first_name' => 'John', 'email' => 'john@example.com'])
    ->redirectUrl('https://example.com/callback')
    ->create();

return redirect($charge->transactionUrl());
```

## Support

For questions and support, please [open an issue](https://github.com/rickeysxhemz/laravel-tap-payment/issues) on GitHub.