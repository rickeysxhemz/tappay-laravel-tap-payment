<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tap API Keys
    |--------------------------------------------------------------------------
    |
    | Your Tap API keys from the Tap Dashboard (goSell > API Credentials).
    | Use test keys for development and live keys for production.
    |
    | Test keys start with: sk_test_, pk_test_
    | Live keys start with: sk_live_, pk_live_
    |
    */

    'secret_key' => env('TAP_SECRET_KEY'),
    'publishable_key' => env('TAP_PUBLISHABLE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Tap API version and base URL.
    | You should not need to change these unless Tap updates their API.
    |
    */

    'api_version' => 'v2',
    'base_url' => env('TAP_BASE_URL', 'https://api.tap.company/v2/'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for charges when not explicitly specified.
    | Use three-letter ISO currency codes (e.g., KWD, SAR, USD, BHD, OMR).
    |
    */

    'currency' => env('TAP_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook handling. The webhook_secret is used to validate
    | incoming webhook signatures. If not set, the secret_key will be used.
    |
    */

    'webhook_secret' => env('TAP_WEBHOOK_SECRET'),
    'webhook_tolerance' => env('TAP_WEBHOOK_TOLERANCE', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | HTTP Configuration
    |--------------------------------------------------------------------------
    |
    | Configure HTTP client behavior including timeouts and retry logic.
    |
    */

    'timeout' => env('TAP_TIMEOUT', 30),
    'connect_timeout' => env('TAP_CONNECT_TIMEOUT', 10),

];
