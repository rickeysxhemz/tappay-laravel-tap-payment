# Charges

## Creating Charges

### Using Array Syntax

```php
use TapPay\Tap\Facades\Tap;

$charge = Tap::charges()->create([
    'amount' => 100.00,
    'currency' => 'SAR',
    'source' => ['id' => 'src_card'],
    'customer' => [
        'first_name' => 'John',
        'email' => 'john@example.com',
    ],
    'redirect' => ['url' => 'https://example.com/callback'],
]);

// Redirect to payment page
return redirect($charge->transactionUrl());
```

### Using Builder Pattern (Recommended)

```php
$charge = Tap::charges()
    ->amount(10000)           // Amount in smallest unit (100.00 SAR)
    ->currency('SAR')
    ->withCard()
    ->customer([
        'first_name' => 'John',
        'email' => 'john@example.com',
    ])
    ->redirectUrl('https://example.com/callback')
    ->create();
```

## Payment Methods

### Card Payment (Redirect)

```php
$charge = Tap::charges()
    ->amount(10000)
    ->withCard()                    // src_card
    ->redirectUrl($callbackUrl)
    ->create();
```

### Regional Payment Methods

```php
// Kuwait - KNET
->withKNET()

// Saudi Arabia - Mada
->withMADA()

// Bahrain - Benefit
->withBenefit()

// Oman - OmanNet
->withOmanNet()

// Qatar - NAPS
->withNAPS()

// All enabled methods
->withAllMethods()
```

### Tokenized Card (Saved Card)

```php
$charge = Tap::charges()
    ->amount(5000)
    ->withToken('tok_xxxxx')        // Token from saved card
    ->redirectUrl($callbackUrl)
    ->create();
```

## Builder Methods Reference

### Core Methods

| Method | Description |
|--------|-------------|
| `amount(int)` | Amount in smallest currency unit |
| `currency(string)` | Three-letter currency code |
| `source(string)` | Payment source ID |
| `description(string)` | Charge description |

### Customer Methods

| Method | Description |
|--------|-------------|
| `customer(array)` | Full customer data |
| `customerId(string)` | Existing customer ID |

### URL Methods

| Method | Description |
|--------|-------------|
| `redirectUrl(string)` | Where to redirect after payment |
| `postUrl(string)` | Webhook URL for this charge |

### Card Methods

| Method | Description |
|--------|-------------|
| `saveCard(bool)` | Save card for future use |
| `statementDescriptor(string)` | Text on customer statement |

### Receipt Methods

| Method | Description |
|--------|-------------|
| `emailReceipt(bool)` | Send email receipt |
| `smsReceipt(bool)` | Send SMS receipt |

### Reference Methods

| Method | Description |
|--------|-------------|
| `reference(string)` | Transaction reference |
| `orderReference(string)` | Order reference |
| `idempotent(string)` | Idempotent key to prevent duplicate charges |
| `metadata(array)` | Custom metadata |
| `addMetadata(key, value)` | Add single metadata item |

### Security Methods

| Method | Description |
|--------|-------------|
| `threeDSecure(bool)` | Enable/disable 3DS |
| `customerInitiated(bool)` | CIT vs MIT flag |
| `authentication(array)` | External 3DS data |
| `authenticationDetails(...)` | 3DS parameters |

### Transaction Methods

| Method | Description |
|--------|-------------|
| `paymentAgreement(id, type)` | For recurring payments |
| `contract(id, type)` | Payment agreement contract |
| `totalPaymentsCount(int)` | Expected payment count |
| `expiresIn(minutes)` | Transaction expiry |

### Marketplace Methods

| Method | Description |
|--------|-------------|
| `merchant(string)` | Merchant ID |
| `platform(string)` | Platform ID |
| `destinations(array)` | Payment splitting |
| `addDestination(id, amount)` | Add single destination |

## Retrieving Charges

```php
// Get a single charge
$charge = Tap::charges()->retrieve('chg_xxxxx');

// Check status
if ($charge->isSuccessful()) {
    // Payment captured
}

// Get charge details
$charge->id();
$charge->status();
$charge->amount();
$charge->currency();
$charge->customerId();
$charge->cardId();          // If card was saved
```

## Charge Statuses

| Status | Description |
|--------|-------------|
| `INITIATED` | Waiting for customer action |
| `CAPTURED` | Payment successful |
| `AUTHORIZED` | Funds held (not captured) |
| `FAILED` | Payment failed |
| `CANCELLED` | Customer cancelled |
| `DECLINED` | Card declined |
| `ABANDONED` | Customer didn't complete |
| `TIMEDOUT` | Transaction expired |

## Idempotency (Preventing Double Charges)

Use the `idempotent()` method to prevent duplicate charges when customers click "Pay" multiple times or when network issues cause retries.

### How It Works

- If the same idempotent key is sent within **24 hours**, Tap returns the original response
- No new charge is created for duplicate requests
- Works with charges, authorizations, and refunds

### Basic Usage

```php
$charge = Tap::charges()
    ->amount(10000)
    ->withCard()
    ->idempotent($order->id)        // Use order ID as idempotent key
    ->redirectUrl($callbackUrl)
    ->create();
```

### Recommended Patterns

```php
// Pattern 1: Use order ID directly
->idempotent($order->id)

// Pattern 2: Prefix with type for clarity
->idempotent("order_{$order->id}")

// Pattern 3: Combine with all references
->reference("txn_{$order->id}")
->orderReference($order->id)
->idempotent($order->id)
```

### Use Cases

| Scenario | Solution |
|----------|----------|
| Double-click on Pay button | Same idempotent key returns original charge |
| Network timeout + retry | Same idempotent key prevents second charge |
| Abandoned 3DS flow | Same key redirects to original 3DS page (within 30 min) |
| Subscription renewal retry | Same key prevents duplicate renewal charge |

### Best Practices

1. **Always use idempotent keys** for production charges
2. **Tie to your order ID** - ensures consistency across retries
3. **Use the same key** when retrying failed network requests
4. **Don't generate random keys** for the same payment intent

## Complete Example

```php
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Exceptions\ApiErrorException;

class PaymentController extends Controller
{
    public function checkout()
    {
        try {
            $charge = Tap::charges()
                ->amount(15000)                     // 150.00 SAR
                ->currency('SAR')
                ->withCard()
                ->customer([
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => [
                        'country_code' => '966',
                        'number' => '500000000',
                    ],
                ])
                ->description('Order #' . $orderId)
                ->reference('order_' . $orderId)
                ->idempotent($orderId)              // Prevent double charges
                ->redirectUrl(route('payment.callback'))
                ->postUrl(route('webhook.tap'))
                ->saveCard()
                ->emailReceipt()
                ->metadata(['order_id' => $orderId])
                ->create();

            // Store charge ID in session
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
            // Update order status
            return redirect()->route('order.success');
        }

        return redirect()->route('order.failed')
            ->withErrors(['payment' => 'Payment failed']);
    }
}
```