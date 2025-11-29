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

    'key' => env('TAP_KEY'),
    'secret' => env('TAP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Tap API base URL.
    | You should not need to change this unless Tap updates their API.
    |
    */

    'base_url' => env('TAP_BASE_URL', 'https://api.tap.company/v2/'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure route registration. Set register_routes to false to disable
    | automatic route registration (Octane-safe alternative to ignoreRoutes).
    | The path is the URI where Tap routes will be registered.
    |
    */

    'register_routes' => env('TAP_REGISTER_ROUTES', true),
    'path' => env('TAP_PATH', 'tap'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency & Country
    |--------------------------------------------------------------------------
    |
    | The default currency for charges when not explicitly specified.
    | Use three-letter ISO currency codes (e.g., KWD, SAR, USD, BHD, OMR).
    | The default country code is used for phone numbers (e.g., 966 for Saudi).
    |
    */

    'currency' => env('TAP_CURRENCY', 'SAR'),
    'default_country_code' => env('TAP_DEFAULT_COUNTRY_CODE', '966'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook handling. The secret is used to validate incoming
    | webhook signatures. If not set, the main secret key will be used.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Redirect Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default redirect URLs for the payment callback controller.
    | These are used when no redirect parameter is provided in the request.
    |
    */

    'redirect' => [
        'success' => env('TAP_REDIRECT_SUCCESS', '/'),
        'failure' => env('TAP_REDIRECT_FAILURE', '/'),
    ],

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
