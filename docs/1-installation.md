# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 11.x or 12.x

## Installation via Composer

```bash
composer require tappay/laravel-tap-payment
```

## Publish Configuration

Publish the configuration file to customize settings:

```bash
php artisan vendor:publish --tag=tap-config
```

This creates `config/tap.php` in your application.

## Database Migration

If you plan to use the Billable trait, add the required column to your users table:

```bash
php artisan make:migration add_tap_customer_id_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('tap_customer_id')->nullable()->index();
    });
}
```

Run the migration:

```bash
php artisan migrate
```

## Verify Installation

Test that the package is installed correctly:

```php
use TapPay\Tap\Facades\Tap;

// This should return the Tap instance
$tap = Tap::getFacadeRoot();
```