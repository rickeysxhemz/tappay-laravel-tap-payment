# Saved Cards (One-Click Payments)

## Overview

Save customer cards for future one-click payments without requiring card details again.

## How It Works

1. **First Payment**: Customer completes payment with `save_card: true`
2. **Card Storage**: Tap returns a card ID (`card_xxxxx`)
3. **Future Payments**: Create a token from card ID, then charge

**Important**: You cannot charge a card ID directly. You must create a token first.

## Database Setup

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->string('tap_customer_id')->nullable()->index();
    $table->string('saved_card_id')->nullable();
    $table->timestamp('card_saved_at')->nullable();
});
```

## Saving a Card

### During First Payment

```php
$charge = Tap::charges()
    ->amount(10000)
    ->withCard()
    ->customer([
        'first_name' => $user->name,
        'email' => $user->email,
    ])
    ->redirectUrl(route('payment.callback'))
    ->saveCard()                    // Enable card saving
    ->create();

return redirect($charge->transactionUrl());
```

### After Payment Callback

```php
public function callback()
{
    $charge = Tap::charges()->retrieve(session('charge_id'));

    if ($charge->isSuccessful()) {
        $cardId = $charge->cardId();

        if ($cardId) {
            auth()->user()->update([
                'saved_card_id' => $cardId,
                'card_saved_at' => now(),
            ]);
        }
    }
}
```

## Charging a Saved Card

### Step 1: Create Token

```php
$token = Tap::tokens()->create([
    'card' => $user->saved_card_id,
    'customer' => $user->tap_customer_id,
]);
```

### Step 2: Charge Token

```php
$charge = Tap::charges()
    ->amount(5000)
    ->withToken($token->id())
    ->redirectUrl(route('payment.callback'))
    ->create();
```

### Using Billable Trait

```php
// Simpler with Billable trait
$token = $user->createCardToken($user->saved_card_id);

$charge = $user->charge(5000, 'SAR', [
    'source' => ['id' => $token->id()],
    'redirect' => ['url' => route('payment.callback')],
]);
```

## Complete Flow Example

```php
class SavedCardController extends Controller
{
    /**
     * First payment - save card
     */
    public function firstPayment()
    {
        $user = auth()->user();

        $charge = $user->newCharge(10000, 'SAR')
            ->withCard()
            ->redirectUrl(route('payment.callback'))
            ->saveCard()
            ->create();

        session(['charge_id' => $charge->id()]);
        return redirect($charge->transactionUrl());
    }

    /**
     * Handle callback
     */
    public function callback()
    {
        $charge = Tap::charges()->retrieve(session('charge_id'));

        if ($charge->isSuccessful()) {
            $cardId = $charge->cardId();

            if ($cardId) {
                auth()->user()->update([
                    'saved_card_id' => $cardId,
                    'card_saved_at' => now(),
                ]);

                return redirect()->route('dashboard')
                    ->with('success', 'Card saved!');
            }
        }

        return redirect()->route('payment.failed');
    }

    /**
     * Pay with saved card
     */
    public function payWithSavedCard()
    {
        $user = auth()->user();

        if (!$user->saved_card_id) {
            return back()->withErrors(['error' => 'No saved card']);
        }

        // Create token from saved card
        $token = $user->createCardToken($user->saved_card_id);

        // Charge
        $charge = $user->newCharge(5000, 'SAR')
            ->withToken($token->id())
            ->redirectUrl(route('payment.callback'))
            ->create();

        session(['charge_id' => $charge->id()]);
        return redirect($charge->transactionUrl());
    }

    /**
     * Remove saved card
     */
    public function removeCard()
    {
        auth()->user()->update([
            'saved_card_id' => null,
            'card_saved_at' => null,
        ]);

        return back()->with('success', 'Card removed');
    }
}
```

## Common Issues

### "Card ID cannot be charged directly"

```php
// Wrong - charging card ID directly
$charge = Tap::charges()->create([
    'source' => ['id' => 'card_xxxxx'],  // Won't work
]);

// Correct - create token first
$token = Tap::tokens()->create([
    'card' => 'card_xxxxx',
    'customer' => 'cus_xxxxx',
]);

$charge = Tap::charges()->create([
    'source' => ['id' => $token->id()],  // Works
]);
```

### "Token already used"

Tokens are single-use. Create a new token for each payment:

```php
// Each payment needs a new token
$token1 = $user->createCardToken($cardId);
$charge1 = $user->charge(100, ...);

$token2 = $user->createCardToken($cardId);  // New token
$charge2 = $user->charge(200, ...);
```

### "Customer not found"

Ensure customer is created before saving card:

```php
// With Billable trait (automatic)
$user->charge(100, 'SAR');  // Creates customer if needed

// Manual
if (!$user->tap_customer_id) {
    $user->createAsTapCustomer();
}
```

## Security Best Practices

1. **Verify payment before saving card**
   ```php
   $charge = Tap::charges()->retrieve($chargeId);
   if ($charge->isSuccessful()) {
       $user->update(['saved_card_id' => $charge->cardId()]);
   }
   ```

2. **Validate customer ownership**
   ```php
   if ($user->tap_customer_id !== $customerId) {
       throw new UnauthorizedException();
   }
   ```

3. **Handle expired cards**
   ```php
   try {
       $token = $user->createCardToken($cardId);
   } catch (InvalidRequestException $e) {
       $user->update(['saved_card_id' => null]);
       return back()->withErrors(['error' => 'Card expired']);
   }
   ```

## Test Cards

| Card Number | Result |
|-------------|--------|
| 4111 1111 1111 1111 | Success |
| 4000 0000 0000 0002 | Declined |
| CVV: 123 | Any |
| Expiry: Any future date | |