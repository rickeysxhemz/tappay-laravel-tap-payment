# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.0] - 2025-01-09

### Added
- Multi-tenant webhook support
- Marketplace methods: `merchants()`, `destinations()`, `payouts()`

### Improved
- Webhook security hardening
- Laravel Octane compatibility

## [1.2.0] - 2024-12-30

### Added
- Initial release of the Laravel Tap Payment package
- **Services**
  - ChargeService for creating, retrieving, updating, and listing charges
  - CustomerService for full CRUD customer management
  - RefundService for processing refunds
  - AuthorizeService for authorization and capture flows
  - TokenService for card tokenization
  - CardService for saved card management
  - InvoiceService for invoice operations
  - SubscriptionService for recurring payments
  - MerchantService for marketplace sub-merchant management
  - DestinationService for payment split destinations
  - PayoutService for merchant settlement tracking

- **Service Architecture**
  - AbstractService base class with common HTTP operations
  - Trait-based CRUD operations for code reuse
    - `HasCreateOperation` - Standard create operations
    - `HasRetrieveOperation` - Standard retrieve operations
    - `HasUpdateOperation` - Standard update operations
    - `HasListOperation` - Standard list operations
    - `HasDeleteOperation` - Standard delete operations
  - Aggregate traits for common patterns
    - `HasCrudOperations` - Full CRUD (Customer, Invoice, Merchant)
    - `HasStandardOperations` - CRUD without delete (Charge, Refund, Authorize, Subscription)
    - `HasReadOperations` - Read-only (Destination, Payout)
    - `HasCreateReadOperations` - Create and read (Token)

- **Builders**
  - ChargeBuilder for fluent charge creation
  - Support for all Tap payment methods (KNET, MADA, Benefit, etc.)
  - Value objects for type-safe parameters (Money, Source, Customer, Destination, Authentication)

- **Events**
  - `PaymentSucceeded` - Dispatched when payment is successful
  - `PaymentFailed` - Dispatched when payment fails
  - `PaymentRetrievalFailed` - Dispatched when charge retrieval fails (API/auth errors)
  - `WebhookReceived` - Dispatched when valid webhook is received
  - `WebhookValidationFailed` - Dispatched when webhook validation fails
  - `WebhookProcessingFailed` - Dispatched when webhook processing throws exception

- **Security**
  - HMAC-SHA256 webhook signature validation
  - Timing-safe signature comparison (`hash_equals`)
  - Replay attack prevention with timestamp tolerance
  - Open redirect protection middleware (`VerifyRedirectUrl`)
  - Input validation on all builder methods
  - `#[SensitiveParameter]` attribute for API keys

- **Billable Trait**
  - `Billable` trait for Eloquent models
  - `HasTapCustomer` for customer management
  - `HasPaymentMethods` for saved cards
  - `Chargeable` for direct charging

- **Testing**
  - Comprehensive test suite with Pest (499+ tests)
  - Unit tests for all resources, builders, and value objects
  - Feature tests for services, routes, and webhooks

### Changed
- Refactored services to use trait-based CRUD operations (reduced ~50% code duplication)

### Security
- All webhook signatures validated with HMAC-SHA256
- Timing-safe comparison prevents timing attacks
- URL redirect validation prevents open redirect vulnerabilities
- No sensitive data logged or exposed in errors