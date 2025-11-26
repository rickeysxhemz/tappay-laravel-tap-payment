# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package for integrating with the Tap Payments v2 API. The package provides a fluent, Laravel-style SDK for processing payments, managing customers, handling refunds, and validating webhooks.

**Package Name**: `tappay/laravel-tap-payment`
**Namespace**: `TapPay\Tap`
**Target**: Laravel 11+ / 12+ with PHP 8.2+

## Architecture

### Service-Resource Pattern

The package follows a service-resource architecture pattern:

- **Services** (`src/Services/`): Handle API communication and business logic
  - `AbstractService`: Base class for all services with common HTTP client setup
  - `ChargeService`, `AuthorizeService`, `CustomerService`, `RefundService`, `TokenService`: Domain-specific API operations

- **Resources** (`src/Resources/`): Represent API response objects
  - `Charge`, `Authorize`, `Customer`, `Refund`, `Token`: Model-like classes that wrap API responses

- **Builders** (`src/Builders/`): Provide fluent interfaces for constructing complex requests
  - `AbstractBuilder`: Base builder with common methods
  - `ChargeBuilder`: Fluent API for building charge requests

### HTTP Client

- `src/Http/Client.php`: Guzzle-based HTTP client wrapper
- Handles API authentication via Bearer token (Secret Key)
- Base URL: `https://api.tap.company/v2/`

### Laravel Integration

- **Service Provider**: `src/TapServiceProvider.php` - Registers services, publishes config, loads routes
- **Main Class**: `src/Tap.php` - Central service container providing access to all services
- **Facade**: `src/Facades/Tap.php` - Single facade providing static access to Tap class
- **Config**: Published to `config/tap.php` (define in `config/` directory)
- **Routes**: Webhook routes should be defined in `routes/` directory

**Facade Pattern**: Use a SINGLE facade (not separate facades for each service). Follow Laravel conventions like Cashier:
```php
// Correct - Single facade with service accessors
Tap::charges()->create([...]);
Tap::customers()->create([...]);
Tap::refunds()->create([...]);

// Incorrect - Don't create TapCharge, TapCustomer, TapRefund facades
```

**Main Tap class structure**:
```php
class Tap {
    public function charges(): ChargeService
    public function customers(): CustomerService
    public function refunds(): RefundService
    public function authorizations(): AuthorizeService
    public function tokens(): TokenService
}
```

### Billable Trait

- **Concern**: `src/Concerns/Billable.php` - Trait to add to User models
- **Contract**: `src/Contracts/Billable.php` - Interface defining billable behavior
- Provides methods like `charge()`, `createAsCustomer()`, `savedCards()` etc.

### Webhooks

- **Controller**: `src/Webhooks/WebhookController.php` - Handles incoming webhook requests
- **Validator**: `src/Webhooks/WebhookValidator.php` - Validates webhook signatures using HMAC-SHA256
- Signature verification uses the Secret Key to validate the `x-tap-signature` header

### Enums

- `ChargeStatus`: INITIATED, CAPTURED, AUTHORIZED, CANCELLED, FAILED
- `RefundStatus`: INITIATED, PENDING, SUCCEEDED, FAILED, CANCELLED
- `SourceObject`: All supported payment methods (expand as needed):
  - **Card Payments**: `SRC_CARD` (redirect to card form), `SRC_ALL` (all enabled methods)
  - **Regional Methods**:
    - `SRC_KNET` (`src_kw.knet`) - Kuwait
    - `SRC_KFAST` (`src_kw.kfast`) - Kuwait fast payment
    - `SRC_MADA` (`src_sa.mada`) - Saudi Arabia
    - `SRC_BENEFIT` (`src_bh.benefit`) - Bahrain
    - `SRC_OMANNET` (`src_om.omannet`) - Oman
    - `SRC_NAPS` (`src_qa.naps`) - Qatar
    - `SRC_FAWRY` (`src_eg.fawry`) - Egypt
  - **Digital Wallets**: `SRC_STC_PAY` - Saudi Arabia
  - **BNPL**: `SRC_TABBY`, `SRC_DEEMA`
  - **Token/Auth**: `TOKEN` (`tok_...`), `AUTH` (`auth_...`)

**Note**: Apple Pay, Google Pay, and Samsung Pay require SDK integration and generate tokens, not direct source IDs.

### Exceptions

- `ApiErrorException`: General API errors
- `AuthenticationException`: API authentication failures
- `InvalidRequestException`: Invalid request parameters

## Key Tap Payments Concepts

### Authorize & Capture Flow (Two-Step Payment)

1. Create authorization via `POST /v2/authorize/` → returns `auth_...` ID (places hold on funds)
2. Capture by creating a charge with `source.id = auth_...` (actually charges the card)

### Saved Card Tokenization

Saved cards cannot be charged directly. The flow is:
1. Save card during first charge with `save_card: true` → get `card_...` ID
2. Create token via `POST /v2/tokens` with `card_id` and `customer_id` → get `tok_...`
3. Create charge with `source.id = tok_...`

### Payment Flow Types

The package supports all Tap Payments payment methods through different flow types:

1. **Redirect Flow** (returns `transaction.url`):
   - Card payments: `src_card`, `src_all`
   - Regional gateways: `src_kw.knet`, `src_sa.mada`, `src_bh.benefit`, `src_om.omannet`, `src_qa.naps`
   - Digital wallets: `src_stcpay`, `src_eg.fawry`
   - BNPL: `src_tabby`, `src_deema`
   - User completes payment on Tap's hosted page or gateway page

2. **Direct Flow** (immediate processing):
   - Tokenized cards: `tok_...` (from saved card or Apple Pay/Google Pay/Samsung Pay SDK)
   - No redirect required, response contains immediate status

3. **Capture Flow** (two-step payment):
   - Authorization reference: `auth_...`
   - Captures previously authorized funds

**Apple Pay/Google Pay/Samsung Pay Integration**:
- These require SDK integration on the client side
- SDKs generate payment tokens (`tok_...`)
- Tokens are then used in direct flow charges
- Package should provide helper methods for processing these tokens

## Package Design Decisions

### Payment Method Support
The package handles ALL Tap Payments methods through the unified Charges API:
- Regional payment methods (KNET, MADA, Benefit, etc.) use `source.id` parameter
- All methods flow through the same `ChargeService` - no separate services per payment method
- ChargeBuilder provides fluent methods for different payment types

### Single Facade Pattern
Following Laravel ecosystem conventions (Cashier, Socialite, Sanctum):
- One `Tap` facade for the entire package
- Services accessed via methods: `Tap::charges()`, `Tap::customers()`, etc.
- Avoids namespace pollution and maintains clean API
- Billable trait provides convenient model methods: `$user->charge()`

### No Application-Level Auth
The package handles only:
- API authentication to Tap servers (API keys)
- Webhook signature validation
- Payment processing

Application developers handle:
- User authentication/sessions
- User authorization/permissions
- Business logic for when to charge
- Route protection

## Development Commands

Since this is a Laravel package, typical development workflow:

```bash
# Install dependencies
composer install

# Run tests (when phpunit.xml is configured)
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Feature/ChargeTest.php

# Run tests with filter
vendor/bin/phpunit --filter testChargeCreation
```

## Testing Notes

- Tests are in `tests/Feature/` and `tests/Unit/`
- `TestCase.php` extends Orchestra Testbench for package testing
- Mock Tap API responses in tests, don't make real API calls
- Test webhook signature validation with known Secret Keys

## Configuration

The package expects a configuration file with:
- `secret_key`: Tap API secret key (sk_test_... or sk_live_...)
- `publishable_key`: Tap API publishable key (pk_test_... or pk_live_...)
- `webhook_secret`: Optional separate webhook validation key
- `currency`: Default currency (e.g., "KWD", "SAR", "USD")

## Important API Details

- **API Authentication**: All requests use `Authorization: Bearer {secret_key}` header
- **Minimum charge**: 0.100 in currency units
- **Webhook validation**: HMAC-SHA256 signature in `x-tap-signature` header
- **Redirect verification**: Always verify final status via `GET /v2/charges/{charge_id}` after redirect callback
- **IDs**: `chg_...` (charge), `auth_...` (authorization), `cus_...` (customer), `card_...` (saved card), `tok_...` (token), `ref_...` (refund)
