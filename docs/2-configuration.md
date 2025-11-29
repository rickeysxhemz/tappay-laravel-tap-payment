# Configuration

## Environment Variables

Add your Tap API credentials to `.env`:

```env
# API Keys
TAP_KEY=pk_test_your_publishable_key
TAP_SECRET=sk_test_your_secret_key

# Default currency
TAP_CURRENCY=SAR

# Webhook (optional - uses TAP_SECRET if not set)
TAP_WEBHOOK_SECRET=your_webhook_secret

# Route path (default: tap)
TAP_PATH=tap

# Redirect URLs (optional)
TAP_REDIRECT_SUCCESS=/payment/success
TAP_REDIRECT_FAILURE=/payment/failed
```

## API Keys

Tap provides two types of API keys:

| Key Type | Prefix | Usage |
|----------|--------|-------|
| **Publishable Key** | `pk_test_*` / `pk_live_*` | Frontend (Tap.js, payment forms) |
| **Secret Key** | `sk_test_*` / `sk_live_*` | Backend API calls |

### Test vs Live Keys

- **Test keys** (`pk_test_*`, `sk_test_*`): Use for development and testing
- **Live keys** (`pk_live_*`, `sk_live_*`): Use for production

No code changes needed to switch - just update environment variables.

## Configuration File

The full configuration file (`config/tap.php`):

```php
return [
    // API Keys
    'key' => env('TAP_KEY'),              // Publishable key (frontend)
    'secret' => env('TAP_SECRET'),        // Secret key (backend)

    // API Settings
    'base_url' => env('TAP_BASE_URL', 'https://api.tap.company/v2/'),

    // Route path for webhooks and callbacks
    'path' => env('TAP_PATH', 'tap'),

    // Default currency
    'currency' => env('TAP_CURRENCY', 'SAR'),
    'default_country_code' => env('TAP_DEFAULT_COUNTRY_CODE', '966'),

    // Webhook configuration
    'webhook' => [
        'secret' => env('TAP_WEBHOOK_SECRET'),
        'tolerance' => env('TAP_WEBHOOK_TOLERANCE', 300),
        'allowed_resources' => [
            'charge', 'refund', 'customer', 'authorize', 'token',
        ],
        'messages' => [
            'invalid_signature' => 'Invalid signature',
            'invalid_payload' => 'Invalid JSON payload',
            'expired' => 'Webhook expired',
            'success' => 'Webhook received',
        ],
    ],

    // Redirect URLs
    'redirect' => [
        'success' => env('TAP_REDIRECT_SUCCESS', '/'),
        'failure' => env('TAP_REDIRECT_FAILURE', '/'),
    ],

    // HTTP Client settings
    'timeout' => env('TAP_TIMEOUT', 30),
    'connect_timeout' => env('TAP_CONNECT_TIMEOUT', 10),
];
```

## Accessing Configuration

```php
// Using config helper
$currency = config('tap.currency');
$secret = config('tap.secret');

// Using Tap helper methods
use TapPay\Tap\Tap;

$publishableKey = Tap::key();   // For frontend
$secretKey = Tap::secret();     // For backend
```

## Disabling Routes

By default, the package registers routes for webhooks and callbacks. To disable:

```php
// In AppServiceProvider.php
use TapPay\Tap\Tap;

public function register(): void
{
    Tap::ignoreRoutes();
}
```

Then define your own routes manually.

## Supported Currencies

| Currency | Code | Decimals | Region |
|----------|------|----------|--------|
| Saudi Riyal | SAR | 2 | Saudi Arabia |
| Kuwaiti Dinar | KWD | 3 | Kuwait |
| Bahraini Dinar | BHD | 3 | Bahrain |
| Omani Rial | OMR | 3 | Oman |
| Qatari Riyal | QAR | 2 | Qatar |
| UAE Dirham | AED | 2 | UAE |
| Egyptian Pound | EGP | 2 | Egypt |
| US Dollar | USD | 2 | Global |