<?php

declare(strict_types=1);

use Carbon\Carbon;
use TapPay\Tap\Enums\InvoiceStatus;
use TapPay\Tap\Exceptions\InvalidDateTimeException;
use TapPay\Tap\Resources\Invoice;
use TapPay\Tap\ValueObjects\Money;

test('can create invoice resource from array', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice)->toBeInstanceOf(Invoice::class);
})->group('unit');

test('can get invoice ID', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->id())->toBe('inv_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get invoice amount', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->amount())->toBeInstanceOf(Money::class)
        ->and($invoice->amount()->toDecimal())->toBe(100.0)
        ->and($invoice->amount()->currency)->toBe('SAR');
})->group('unit');

test('can get invoice currency', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->currency())->toBe('SAR');
})->group('unit');

test('can get invoice status', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->status())->toBeInstanceOf(InvoiceStatus::class)
        ->and($invoice->status()->value)->toBe('PENDING');
})->group('unit');

test('can get invoice description', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->description())->toBe('Monthly subscription invoice');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get invoice URL', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->url())->toStartWith('https://sandbox.checkout.tap.company');
})->group('unit');

test('can get expiresAt as Carbon', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->expiresAt())->toBeInstanceOf(Carbon::class);
})->group('unit');

test('can get invoice metadata', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->metadata())->toBeArray()
        ->and($invoice->metadata()['subscription_id'])->toBe('sub_12345');
})->group('unit');

// Status helper tests
test('isSuccessful returns true for PAID status', function () {
    $invoice = new Invoice(['status' => 'PAID']);

    expect($invoice->isSuccessful())->toBeTrue();
})->group('unit');

test('isPending returns true for PENDING status', function () {
    $invoice = new Invoice(['status' => 'PENDING']);

    expect($invoice->isPending())->toBeTrue();
})->group('unit');

test('hasFailed returns true for FAILED status', function () {
    $invoice = new Invoice(['status' => 'FAILED']);

    expect($invoice->hasFailed())->toBeTrue();
})->group('unit');

test('isExpired returns true for EXPIRED status', function () {
    $invoice = new Invoice(['status' => 'EXPIRED']);

    expect($invoice->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns false for PENDING status', function () {
    $invoice = new Invoice(['status' => 'PENDING']);

    expect($invoice->isExpired())->toBeFalse();
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid invoice ID', function () {
    $invoice = new Invoice(['id' => 'inv_12345']);

    expect($invoice->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $invoice = new Invoice([]);

    expect($invoice->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without inv prefix', function () {
    $invoice = new Invoice(['id' => 'chg_12345']);

    expect($invoice->hasValidId())->toBeFalse();
})->group('unit');

// Date parsing tests
test('paidAt returns Carbon when paid_at is set', function () {
    $invoice = new Invoice(['paid_at' => '2025-01-15T10:30:00Z']);

    expect($invoice->paidAt())->toBeInstanceOf(Carbon::class);
})->group('unit');

test('paidAt returns null when not paid', function () {
    $invoice = new Invoice(['paid_at' => null]);

    expect($invoice->paidAt())->toBeNull();
})->group('unit');

test('expiresAt throws exception for invalid date', function () {
    $invoice = new Invoice(['expiry' => 'invalid-date']);

    expect(fn () => $invoice->expiresAt())->toThrow(InvalidDateTimeException::class);
})->group('unit');

test('chargeId returns charge ID when available', function () {
    $invoice = new Invoice(['charge_id' => 'chg_12345']);

    expect($invoice->chargeId())->toBe('chg_12345');
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $invoice = new Invoice([]);

    expect($invoice->id())->toBe('');
})->group('unit');

test('throws exception for missing amount', function () {
    $invoice = new Invoice(['currency' => 'SAR']);

    expect(fn () => $invoice->amount())->toThrow(TapPay\Tap\Exceptions\InvalidAmountException::class);
})->group('unit');

test('uses default currency from config when not provided', function () {
    config(['tap.currency' => 'KWD']);
    $invoice = new Invoice(['amount' => 100.0]);

    expect($invoice->currency())->toBe('KWD');
})->group('unit');

test('returns FAILED for missing status', function () {
    $invoice = new Invoice([]);

    expect($invoice->status())->toBe(InvoiceStatus::FAILED);
})->group('unit');

test('returns null for missing description', function () {
    $invoice = new Invoice([]);

    expect($invoice->description())->toBeNull();
})->group('unit');

test('returns null for missing customerId', function () {
    $invoice = new Invoice([]);

    expect($invoice->customerId())->toBeNull();
})->group('unit');

test('returns null for missing url', function () {
    $invoice = new Invoice([]);

    expect($invoice->url())->toBeNull();
})->group('unit');

test('returns null for missing expiresAt', function () {
    $invoice = new Invoice([]);

    expect($invoice->expiresAt())->toBeNull();
})->group('unit');

test('returns empty array for missing metadata', function () {
    $invoice = new Invoice([]);

    expect($invoice->metadata())->toBe([]);
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('invoice.json');
    $invoice = new Invoice($data);

    expect($invoice->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $invoice = new Invoice(['id' => 'inv_123']);

    expect($invoice->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $invoice = new Invoice([]);

    expect($invoice->isEmpty())->toBeTrue();
})->group('unit');
