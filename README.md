# Laravel Tap Payments SDK

A modern, fluent Laravel package for integrating with the Tap Payments v2 API. Supports all MENA payment methods including KNET, MADA, Benefit, and more.

## Features

- 🚀 Fluent builder pattern for creating charges
- 💳 Support for all Tap payment methods (Cards, KNET, MADA, Benefit, BNPL, etc.)
- 🔐 Secure webhook validation with event-driven architecture
- 👤 Billable trait for Eloquent models (Laravel Cashier pattern)
- ✅ Comprehensive testing support
- 📦 Laravel 11 & 12 compatible
- 🎯 Full type safety with PHP 8.2+
- ⚡ Laravel Octane compatible (100% safe for Swoole/RoadRunner)

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

Webhooks are automatically registered at `/tap/webhook`. The package uses an event-driven architecture for maximum flexibility.

#### Available Webhook Events

**Event Classes:**
```php
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Events\WebhookProcessingFailed;
```

**String-based Events:**
- `tap.webhook.charge` - Charge webhook received
- `tap.webhook.refund` - Refund webhook received
- `tap.webhook.customer` - Customer webhook received
- `tap.webhook.authorize` - Authorization webhook received
- `tap.webhook.token` - Token webhook received
- `tap.webhook.received` - Any webhook received (catch-all)

#### Listening to Webhook Events

**Option 1: Using Event Classes (Recommended)**

```php
// In EventServiceProvider
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;

protected $listen = [
    WebhookReceived::class => [
        \App\Listeners\HandleTapWebhook::class,
    ],
    WebhookValidationFailed::class => [
        \App\Listeners\LogFailedWebhook::class,
    ],
];

// Listener
class HandleTapWebhook
{
    public function handle(WebhookReceived $event)
    {
        if ($event->isType('charge')) {
            $chargeId = $event->getId();
            $status = $event->payload['status'];

            // Update your order, etc.
        }
    }
}
```

**Option 2: Using String-based Events**

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

#### Webhook Security & Monitoring

Listen for validation failures and errors:

```php
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Events\WebhookProcessingFailed;

// Log failed webhook validations
Event::listen(WebhookValidationFailed::class, function ($event) {
    Log::warning('Webhook validation failed', [
        'reason' => $event->reason,
        'ip' => $event->ip,
        'context' => $event->context,
    ]);
});

// Handle webhook processing errors
Event::listen(WebhookProcessingFailed::class, function ($event) {
    Log::error('Webhook processing failed', [
        'error' => $event->getErrorMessage(),
        'resource' => $event->resource,
        'webhook_id' => $event->getId(),
    ]);
});
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

### Saved Cards (One-Click Payments)

The package provides complete support for saving customer cards and charging them for future purchases without requiring card details again.

#### How Card Saving Works

1. **First Payment**: Customer enters card details and completes payment with `save_card: true`
2. **Card Storage**: Tap securely stores the card and returns a card ID (`card_xxxxx`)
3. **Future Payments**: Create a token from the saved card ID and charge it
4. **One-Click**: Customer doesn't need to re-enter card details

**Important**: You cannot charge a card ID directly. You must create a token first.

---

#### Database Setup

Add columns to store Tap customer and card IDs:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tap_customer_id')->nullable()->index();
            $table->string('saved_card_id')->nullable();
            $table->timestamp('card_saved_at')->nullable();
        });
    }
};
```

---

#### Option 1: Using Billable Trait (Recommended)

```php
use App\Models\User;
use TapPay\Tap\Exceptions\ApiErrorException;

class PaymentController extends Controller
{
    /**
     * Save card during first payment
     */
    public function firstPayment()
    {
        $user = auth()->user();

        try {
            $charge = $user->charge(100.00, 'KWD', [
                'source' => ['id' => 'src_card'],
                'redirect' => ['url' => route('payment.callback')],
                'save_card' => true,  // ✅ Save the card
                'description' => 'First payment - save card',
            ]);

            session(['tap_charge_id' => $charge->id()]);
            return redirect($charge->transactionUrl());

        } catch (ApiErrorException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle callback and save card ID
     */
    public function paymentCallback()
    {
        $chargeId = session('tap_charge_id');

        try {
            $charge = Tap::charges()->retrieve($chargeId);

            if ($charge->isSuccessful()) {
                // Save card ID to user record
                $cardId = $charge->cardId();

                if ($cardId) {
                    auth()->user()->update([
                        'saved_card_id' => $cardId,
                        'card_saved_at' => now(),
                    ]);

                    return redirect()->route('success')
                        ->with('message', 'Payment successful! Card saved for future use.');
                }
            }

            return redirect()->route('failed')->withErrors(['error' => 'Payment failed']);

        } catch (ApiErrorException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Charge saved card (one-click payment)
     */
    public function chargeWithSavedCard()
    {
        $user = auth()->user();

        if (!$user->saved_card_id) {
            return back()->withErrors(['error' => 'No saved card found']);
        }

        try {
            // Create token from saved card
            $token = $user->createCardToken($user->saved_card_id);

            // Charge using token
            $charge = $user->charge(50.00, 'KWD', [
                'source' => ['id' => $token->id()],
                'redirect' => ['url' => route('payment.callback')],
                'description' => 'Payment with saved card',
            ]);

            session(['tap_charge_id' => $charge->id()]);
            return redirect($charge->transactionUrl());

        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => 'Please add a card first']);
        } catch (ApiErrorException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

---

#### Option 2: Using Builder Pattern

```php
// Save card during first payment
$charge = $user->newCharge(100.00, 'KWD')
    ->withCard()
    ->redirectUrl(route('payment.callback'))
    ->saveCard()  // ✅ Enable card saving
    ->description('First payment')
    ->create();

// After payment, get card ID
$cardId = $charge->cardId();

// Store card ID
auth()->user()->update(['saved_card_id' => $cardId]);

// Future payments with saved card
$token = $user->createCardToken($user->saved_card_id);

$charge = $user->newCharge(50.00, 'KWD')
    ->withToken($token->id())
    ->redirectUrl(route('payment.callback'))
    ->description('Payment with saved card')
    ->create();
```

---

#### Option 3: Direct API (Without Billable Trait)

```php
use TapPay\Tap\Facades\Tap;

// Step 1: Create customer
$customer = Tap::customers()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => [
        'country_code' => '965',
        'number' => '50000000',
    ],
]);

// Step 2: Create charge with save_card
$charge = Tap::charges()->create([
    'amount' => 100.00,
    'currency' => 'KWD',
    'source' => ['id' => 'src_card'],
    'customer' => ['id' => $customer->id()],
    'save_card' => true,  // ✅ Save card
    'redirect' => ['url' => 'https://yoursite.com/callback'],
    'description' => 'First payment',
]);

// Redirect to payment page
return redirect($charge->transactionUrl());

// Step 3: After payment, get card ID
$charge = Tap::charges()->retrieve($chargeId);
$cardId = $charge->cardId();  // 'card_xxxxx'

// Step 4: Create token from saved card
$token = Tap::tokens()->create([
    'card' => $cardId,
    'customer' => $customer->id(),
]);

// Step 5: Charge saved card
$futureCharge = Tap::charges()->create([
    'amount' => 50.00,
    'currency' => 'KWD',
    'source' => ['id' => $token->id()],  // Use token, not card ID
    'customer' => ['id' => $customer->id()],
    'redirect' => ['url' => 'https://yoursite.com/callback'],
]);
```

---

#### Complete Payment Flow with Card Management

```php
class UserCardController extends Controller
{
    /**
     * Display user's saved cards
     */
    public function index()
    {
        $user = auth()->user();

        return view('cards.index', [
            'hasSavedCard' => !empty($user->saved_card_id),
            'cardSavedAt' => $user->card_saved_at,
        ]);
    }

    /**
     * Add new card (without charging)
     */
    public function store()
    {
        $user = auth()->user();

        try {
            // Create a minimal charge to save card
            $charge = $user->charge(0.001, 'KWD', [
                'source' => ['id' => 'src_card'],
                'redirect' => ['url' => route('cards.callback')],
                'save_card' => true,
                'description' => 'Card verification',
            ]);

            session(['action' => 'add_card', 'charge_id' => $charge->id()]);
            return redirect($charge->transactionUrl());

        } catch (ApiErrorException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle card add callback
     */
    public function callback()
    {
        $chargeId = session('charge_id');
        $action = session('action');

        try {
            $charge = Tap::charges()->retrieve($chargeId);

            if ($charge->isSuccessful() && $action === 'add_card') {
                $cardId = $charge->cardId();

                if ($cardId) {
                    auth()->user()->update([
                        'saved_card_id' => $cardId,
                        'card_saved_at' => now(),
                    ]);

                    return redirect()->route('cards.index')
                        ->with('success', 'Card saved successfully!');
                }
            }

            return redirect()->route('cards.index')
                ->withErrors(['error' => 'Failed to save card']);

        } catch (ApiErrorException $e) {
            return redirect()->route('cards.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove saved card
     */
    public function destroy()
    {
        auth()->user()->update([
            'saved_card_id' => null,
            'card_saved_at' => null,
        ]);

        return redirect()->route('cards.index')
            ->with('success', 'Card removed successfully');
    }
}
```

---

#### Webhook Handler for Card Payments

```php
// In EventServiceProvider
use TapPay\Tap\Events\WebhookReceived;

protected $listen = [
    WebhookReceived::class => [
        HandleCardPayment::class,
    ],
];

// Listener
class HandleCardPayment
{
    public function handle(WebhookReceived $event)
    {
        if ($event->isType('charge')) {
            $chargeId = $event->getId();
            $status = $event->payload['status'];
            $customerId = $event->payload['customer']['id'] ?? null;

            // Find user by customer ID
            $user = User::where('tap_customer_id', $customerId)->first();

            if ($user && $status === 'CAPTURED') {
                // Payment successful
                $cardId = $event->payload['card']['id'] ?? null;

                if ($cardId && $event->payload['save_card'] ?? false) {
                    // Card was saved
                    $user->update([
                        'saved_card_id' => $cardId,
                        'card_saved_at' => now(),
                    ]);

                    Log::info("Card saved for user {$user->id}", [
                        'card_id' => $cardId,
                    ]);
                }

                // Process order, send confirmation email, etc.
            }
        }
    }
}
```

---

#### Security Best Practices

**1. Verify Payment Before Saving Card**
```php
// ✅ Good - verify first
$charge = Tap::charges()->retrieve($chargeId);
if ($charge->isSuccessful()) {
    $user->update(['saved_card_id' => $charge->cardId()]);
}

// ❌ Bad - save without verification
$user->update(['saved_card_id' => $request->input('card_id')]);
```

**2. Always Use HTTPS**
```php
// In .env
APP_URL=https://yourdomain.com  // ✅ HTTPS required
```

**3. Validate Customer Ownership**
```php
// ✅ Good - verify user owns the customer
if ($user->tapCustomerId() !== $customerId) {
    throw new UnauthorizedException();
}

// Create token only for user's own cards
$token = $user->createCardToken($cardId);
```

**4. Handle Expired Cards**
```php
try {
    $token = $user->createCardToken($user->saved_card_id);
    $charge = $user->charge(100, 'KWD', [
        'source' => ['id' => $token->id()],
    ]);
} catch (InvalidRequestException $e) {
    // Card expired or invalid
    $user->update(['saved_card_id' => null]);
    return back()->withErrors(['error' => 'Card expired. Please add a new card.']);
}
```

---

#### Testing Card Saving

**Test Card Numbers** (Tap Sandbox):
```
Successful: 4111 1111 1111 1111
Declined:   4000 0000 0000 0002
CVV: 123
Expiry: Any future date
```

**Test Flow**:
```php
// Feature test
public function test_user_can_save_card()
{
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('payment.first'))
        ->assertRedirect();

    // Simulate Tap callback
    $charge = Tap::charges()->create([
        'amount' => 100,
        'currency' => 'KWD',
        'customer' => ['id' => $user->tapCustomerId()],
        'save_card' => true,
    ]);

    $user->refresh();

    $this->assertNotNull($user->saved_card_id);
    $this->assertNotNull($user->card_saved_at);
}
```

---

#### Common Issues & Solutions

**Issue 1: "Card ID cannot be charged directly"**
```php
// ❌ Wrong - charging card ID directly
$charge = Tap::charges()->create([
    'source' => ['id' => 'card_xxxxx'],  // ❌ Won't work
]);

// ✅ Correct - create token first
$token = Tap::tokens()->create([
    'card' => 'card_xxxxx',
    'customer' => 'cus_xxxxx',
]);

$charge = Tap::charges()->create([
    'source' => ['id' => $token->id()],  // ✅ Works
]);
```

**Issue 2: "Customer must be created first"**
```php
// ✅ Billable trait handles this automatically
$user->charge(100, 'KWD');  // Creates customer if needed

// ✅ Manual approach
if (!$user->tapCustomerId()) {
    $user->createAsTapCustomer();
}
```

**Issue 3: "Token already used"**
```php
// Tokens are single-use - create new token each time
$token = $user->createCardToken($cardId);  // ✅ New token
$charge = $user->charge(100, 'KWD', [
    'source' => ['id' => $token->id()],
]);

// For next payment, create another token
$token2 = $user->createCardToken($cardId);  // ✅ New token
```

---

#### UI Example (Blade)

```blade
{{-- resources/views/cards/index.blade.php --}}
<div class="card">
    <h3>Saved Payment Methods</h3>

    @if($hasSavedCard)
        <div class="saved-card">
            <i class="fa fa-credit-card"></i>
            <span>Card saved on {{ $cardSavedAt->format('M d, Y') }}</span>

            <form action="{{ route('cards.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Remove Card</button>
            </form>
        </div>

        <form action="{{ route('payment.saved-card') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                Pay with Saved Card
            </button>
        </form>
    @else
        <p>No saved cards</p>

        <a href="{{ route('cards.add') }}" class="btn btn-primary">
            Add Payment Method
        </a>
    @endif
</div>
```

## Error Handling

All API methods can throw exceptions. Handle them appropriately:

```php
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Exceptions\ApiErrorException;

try {
    $charge = Tap::charges()->create($data);
} catch (InvalidRequestException $e) {
    // Invalid parameters (400, 422)
    Log::error('Invalid charge request', [
        'error' => $e->getMessage(),
        'errors' => $e->getErrors(),
    ]);
} catch (AuthenticationException $e) {
    // Authentication failed (401)
    Log::critical('Tap API authentication failed');
} catch (ApiErrorException $e) {
    // API error or network error
    Log::error('Tap API error', [
        'message' => $e->getMessage(),
        'status' => $e->getStatusCode(),
    ]);
}
```

### Exception Types

- **`InvalidRequestException`** - Invalid request parameters (HTTP 400, 422)
- **`AuthenticationException`** - API authentication failure (HTTP 401)
- **`ApiErrorException`** - General API errors, server errors (HTTP 5xx), or network errors

## Performance & Best Practices

### Pagination for Large Datasets

When listing resources, use pagination to avoid memory issues:

```php
// ❌ Bad - loads all charges
$charges = Tap::charges()->list();

// ✅ Good - paginate results
$charges = Tap::charges()->list([
    'limit' => 20,
    'starting_after' => $lastChargeId,
]);
```

### Caching

The package doesn't cache API responses. Implement your own caching for frequently accessed data:

```php
use Illuminate\Support\Facades\Cache;

$customer = Cache::remember("tap.customer.{$customerId}", 3600, function () use ($customerId) {
    return Tap::customers()->retrieve($customerId);
});
```

### Production Deployment

**Environment Variables:**
```env
TAP_SECRET_KEY=sk_live_your_live_key
TAP_PUBLISHABLE_KEY=pk_live_your_live_key
TAP_CURRENCY=KWD
```

**Webhook URL:** Configure in Tap Dashboard
```
https://yourdomain.com/tap/webhook
```

**Error Monitoring:** Set up exception tracking
```php
// In App\Exceptions\Handler.php
use TapPay\Tap\Exceptions\ApiErrorException;

public function register()
{
    $this->reportable(function (ApiErrorException $e) {
        // Send to error tracking service (Sentry, Bugsnag, etc.)
    });
}
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
