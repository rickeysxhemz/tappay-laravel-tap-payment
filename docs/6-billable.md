# Billable Trait

## Overview

The `Billable` trait adds payment functionality directly to your Eloquent models, similar to Laravel Cashier.

## Setup

### 1. Add Trait to Model

```php
use TapPay\Tap\Concerns\Billable;
use TapPay\Tap\Contracts\Billable as BillableContract;

class User extends Authenticatable implements BillableContract
{
    use Billable;
}
```

### 2. Database Migration

> **Important:** This package does not ship with migrations. You must create your own migration to add the required column.

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

```bash
php artisan migrate
```

## Available Methods

### Customer Management

```php
// Create Tap customer
$user->createAsTapCustomer([
    'first_name' => $user->name,
    'email' => $user->email,
]);

// Get customer ID
$customerId = $user->tapCustomerId();

// Check if customer exists
if ($user->hasTapCustomerId()) {
    // ...
}

// Sync customer data to Tap
$user->syncTapCustomer();
```

### Charging

```php
// Simple charge
$charge = $user->charge(10000, 'SAR', [
    'source' => ['id' => 'src_card'],
    'redirect' => ['url' => route('callback')],
]);

// Using builder
$charge = $user->newCharge(10000, 'SAR')
    ->withCard()
    ->redirectUrl(route('callback'))
    ->saveCard()
    ->create();
```

### Saved Cards

```php
// Create token from saved card
$token = $user->createCardToken($cardId);

// Charge saved card
$charge = $user->charge(5000, 'SAR', [
    'source' => ['id' => $token->id()],
    'redirect' => ['url' => route('callback')],
]);
```

## Auto Customer Creation

When you charge a user without a Tap customer ID, one is created automatically:

```php
$user = User::find(1);
// $user->tap_customer_id is null

$charge = $user->charge(10000, 'SAR', [...]);
// $user->tap_customer_id is now set
```

## Customizing Customer Data

Override methods to customize customer data sent to Tap:

```php
class User extends Authenticatable implements BillableContract
{
    use Billable;

    /**
     * Get first name for Tap customer
     */
    public function tapFirstName(): string
    {
        return $this->first_name ?? explode(' ', $this->name)[0];
    }

    /**
     * Get last name for Tap customer
     */
    public function tapLastName(): ?string
    {
        return $this->last_name ?? explode(' ', $this->name)[1] ?? null;
    }

    /**
     * Get email for Tap customer
     */
    public function tapEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get phone for Tap customer
     */
    public function tapPhone(): ?array
    {
        if (!$this->phone) {
            return null;
        }

        return [
            'country_code' => $this->phone_country_code ?? '966',
            'number' => $this->phone,
        ];
    }
}
```

## Complete Example

```php
class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();
        $amount = $request->input('amount');

        try {
            $charge = $user->newCharge($amount, 'SAR')
                ->withCard()
                ->description('Order #' . $request->order_id)
                ->reference('order_' . $request->order_id)
                ->redirectUrl(route('payment.callback'))
                ->postUrl(route('webhook.tap'))
                ->emailReceipt()
                ->metadata([
                    'order_id' => $request->order_id,
                    'user_id' => $user->id,
                ])
                ->create();

            session(['charge_id' => $charge->id()]);

            return redirect($charge->transactionUrl());

        } catch (ApiErrorException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    public function callback()
    {
        $chargeId = session('charge_id');
        $charge = Tap::charges()->retrieve($chargeId);

        if ($charge->isSuccessful()) {
            // Payment successful
            $order = Order::where('charge_id', $chargeId)->first();
            $order->update(['status' => 'paid']);

            return redirect()->route('order.success', $order);
        }

        return redirect()->route('order.failed')
            ->withErrors(['payment' => 'Payment failed: ' . $charge->status()]);
    }
}
```

## Method Reference

| Method | Description |
|--------|-------------|
| `tapCustomerId()` | Get Tap customer ID |
| `hasTapCustomerId()` | Check if customer exists |
| `createAsTapCustomer(array)` | Create customer in Tap |
| `syncTapCustomer()` | Update customer in Tap |
| `charge(amount, currency, options)` | Create a charge |
| `newCharge(amount, currency)` | Get charge builder |
| `createCardToken(cardId)` | Create token from saved card |
| `tapFirstName()` | Get first name for Tap |
| `tapLastName()` | Get last name for Tap |
| `tapEmail()` | Get email for Tap |
| `tapPhone()` | Get phone array for Tap |

## Using with Other Models

The trait works with any Eloquent model, not just User:

```php
class Company extends Model implements BillableContract
{
    use Billable;

    public function tapFirstName(): string
    {
        return $this->company_name;
    }

    public function tapEmail(): ?string
    {
        return $this->billing_email;
    }
}
```

```php
$company = Company::find(1);
$charge = $company->charge(50000, 'SAR', [...]);
```