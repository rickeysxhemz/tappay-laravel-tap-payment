# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of the Laravel Tap Payment package
- ChargeService for creating, retrieving, and listing charges
- CustomerService for customer management
- RefundService for processing refunds
- AuthorizeService for authorization flows
- TokenService for card tokenization
- CardService for saved card management
- InvoiceService for invoice operations
- SubscriptionService for recurring payments
- Billable trait for Eloquent models
- ChargeBuilder for fluent charge creation
- Webhook handling with signature validation
- Support for all Tap payment methods (KNET, MADA, Benefit, etc.)
- Comprehensive test suite with Pest
- Full documentation