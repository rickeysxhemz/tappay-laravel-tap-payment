# Marketplace & Payment Splits

This package supports Tap Payments' marketplace features for multi-vendor platforms. Split payments automatically between your platform and sub-merchants.

## Overview

Marketplace features allow you to:
- Onboard and manage sub-merchants
- Split payments between multiple parties
- Track settlements and payouts to merchants

## Sub-Merchant Management

### Creating a Sub-Merchant

```php
use TapPay\Tap\Facades\Tap;

$merchant = Tap::merchants()->create([
    'name' => 'Vendor Store',
    'email' => 'vendor@example.com',
    'phone' => [
        'country_code' => '966',
        'number' => '500000000',
    ],
    'country_code' => 'SA',
    'type' => 'company',  // 'individual' or 'company'
    'business' => [
        'name' => 'Vendor Store LLC',
        'type' => 'retail',
    ],
    'bank_account' => [
        'iban' => 'SA0000000000000000000000',
    ],
]);

echo $merchant->id();        // merchant_xxxxx
echo $merchant->name();      // Vendor Store
echo $merchant->isActive();  // true/false
```

### Retrieving a Merchant

```php
$merchant = Tap::merchants()->retrieve('merchant_xxxxx');

if ($merchant->isVerified()) {
    // Merchant can receive payments
}
```

### Updating a Merchant

```php
$merchant = Tap::merchants()->update('merchant_xxxxx', [
    'email' => 'newemail@example.com',
]);
```

### Listing Merchants

```php
$merchants = Tap::merchants()->list([
    'limit' => 25,
    'status' => 'ACTIVE',
]);

foreach ($merchants as $merchant) {
    echo $merchant->name();
}
```

### Deleting (Archiving) a Merchant

```php
Tap::merchants()->delete('merchant_xxxxx');
```

## Payment Splits

Split a single payment across multiple merchants using destinations.

### Using the Destination Value Object

```php
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\ValueObjects\Destination;

$charge = Tap::charges()
    ->amount(10000)  // 100.00 in smallest unit
    ->currency('SAR')
    ->withCard()
    ->customer([
        'first_name' => 'Customer',
        'email' => 'customer@example.com',
    ])
    ->destinations([
        Destination::make('merchant_vendor1', 7000),   // 70.00 to vendor
        Destination::make('merchant_vendor2', 2000),  // 20.00 to another vendor
        // Remaining 10.00 stays with platform
    ])
    ->redirectUrl('https://example.com/callback')
    ->create();
```

### Using Arrays

```php
$charge = Tap::charges()
    ->amount(10000)
    ->withCard()
    ->destinations([
        ['id' => 'merchant_vendor1', 'amount' => 7000],
        ['id' => 'merchant_vendor2', 'amount' => 2000, 'currency' => 'SAR'],
    ])
    ->redirectUrl('https://example.com/callback')
    ->create();
```

### Destination with Different Currency

```php
use TapPay\Tap\ValueObjects\Destination;

$destinations = [
    Destination::make('merchant_123', 50.00, 'USD'),
    Destination::make('merchant_456', 30.00, 'USD'),
];
```

## Tracking Destinations

After a charge with destinations is created, you can track each split.

### List Destinations for a Charge

```php
$destinations = Tap::destinations()->listByCharge('chg_xxxxx');

foreach ($destinations as $dest) {
    echo $dest->merchantId();  // merchant_xxxxx
    echo $dest->amount();      // 70.00
    echo $dest->status();      // PENDING, TRANSFERRED

    if ($dest->isComplete()) {
        echo "Transfer ID: " . $dest->transferId();
    }
}
```

### List Destinations by Merchant

```php
$destinations = Tap::destinations()->listByMerchant('merchant_xxxxx', [
    'limit' => 50,
]);
```

## Payout Tracking

Track when merchants receive their settlements.

### List Payouts for a Merchant

```php
$payouts = Tap::payouts()->listByMerchant('merchant_xxxxx');

foreach ($payouts as $payout) {
    echo $payout->id();              // payout_xxxxx
    echo $payout->amount();          // Gross amount
    echo $payout->feeAmount();       // Platform/Tap fees
    echo $payout->netAmount();       // Amount merchant receives
    echo $payout->status();          // PENDING, IN_PROGRESS, PAID, FAILED
    echo $payout->arrivalDate();     // Expected/actual arrival date
    echo $payout->transactionCount(); // Number of transactions

    if ($payout->isComplete()) {
        echo "Payout completed!";
    }
}
```

### Filter Payouts by Status

```php
$pendingPayouts = Tap::payouts()->list([
    'merchant' => 'merchant_xxxxx',
    'status' => 'PENDING',
]);

$completedPayouts = Tap::payouts()->list([
    'merchant' => 'merchant_xxxxx',
    'status' => 'PAID',
]);
```

### Filter Payouts by Date Range

```php
$payouts = Tap::payouts()->list([
    'merchant' => 'merchant_xxxxx',
    'arrival_date' => [
        'gte' => '2024-01-01',
        'lte' => '2024-01-31',
    ],
]);
```

### Download Payout Report

```php
$report = Tap::payouts()->download([
    'merchant' => 'merchant_xxxxx',
    'period' => [
        'start' => '2024-01-01',
        'end' => '2024-01-31',
    ],
    'format' => 'csv',  // or 'xlsx'
]);
```

## Payout Resource Methods

| Method | Description |
|--------|-------------|
| `id()` | Payout ID |
| `amount()` | Gross payout amount |
| `currency()` | Payout currency |
| `merchantId()` | Receiving merchant ID |
| `status()` | PENDING, IN_PROGRESS, PAID, FAILED |
| `isPending()` | Check if payout is pending or in progress |
| `isComplete()` | Check if payout is paid |
| `isFailed()` | Check if payout failed |
| `feeAmount()` | Fees deducted |
| `netAmount()` | Amount after fees |
| `arrivalDate()` | Expected or actual arrival date |
| `periodStart()` | Settlement period start |
| `periodEnd()` | Settlement period end |
| `transactionCount()` | Number of transactions included |
| `bankAccount()` | Bank account details |

## Merchant Resource Methods

| Method | Description |
|--------|-------------|
| `id()` | Merchant ID |
| `name()` | Merchant name |
| `email()` | Merchant email |
| `phone()` | Phone number array |
| `countryCode()` | Country code (SA, KW, etc.) |
| `type()` | 'individual' or 'company' |
| `status()` | Merchant status |
| `isActive()` | Check if merchant is active |
| `isVerified()` | Check if merchant is verified |
| `business()` | Business details array |
| `bankAccount()` | Bank account details |
| `payoutSchedule()` | Payout frequency |

## Destination Resource Methods

| Method | Description |
|--------|-------------|
| `id()` | Destination ID |
| `merchantId()` | Receiving merchant ID |
| `amount()` | Split amount |
| `currency()` | Split currency |
| `chargeId()` | Associated charge ID |
| `transferId()` | Transfer ID (once settled) |
| `status()` | PENDING, TRANSFERRED |
| `isPending()` | Check if transfer is pending |
| `isComplete()` | Check if transfer is complete |

## Best Practices

1. **Validate Split Amounts**: Ensure destination amounts don't exceed the charge amount
2. **Handle Partial Splits**: The remaining amount stays with your platform account
3. **Monitor Payout Status**: Use webhooks to track payout completions
4. **Verify Merchants**: Only split payments to verified merchants