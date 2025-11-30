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

> **Note:** This package does not include migrations. You must create your own migration if you plan to use the Billable trait.

If you plan to use the Billable trait on your models (e.g., User), add the required column:

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

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('tap_customer_id');
    });
}
```

Run the migration:

```bash
php artisan migrate
```

> **Tip:** You can add the `tap_customer_id` column to any model that uses the Billable trait, not just the User model.

## Verify Installation

Test that the package is installed correctly:

```php
use TapPay\Tap\Facades\Tap;

// This should return the Tap instance
$tap = Tap::getFacadeRoot();
```