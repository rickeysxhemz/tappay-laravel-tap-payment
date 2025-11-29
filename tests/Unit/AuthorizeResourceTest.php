<?php

declare(strict_types=1);

use TapPay\Tap\Enums\AuthorizeStatus;
use TapPay\Tap\Resources\Authorize;
use TapPay\Tap\ValueObjects\Money;

test('can create authorize resource from array', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize)->toBeInstanceOf(Authorize::class);
})->group('unit');

test('can get authorization ID', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->id())->toBe('auth_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get authorization amount', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->amount())->toBeInstanceOf(Money::class)
        ->and($authorize->amount()->toDecimal())->toBe(50.0)
        ->and($authorize->amount()->currency)->toBe('SAR');
})->group('unit');

test('can get authorization currency', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->currency())->toBe('SAR');
})->group('unit');

test('can get authorization status', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->status())->toBeInstanceOf(AuthorizeStatus::class)
        ->and($authorize->status()->value)->toBe('INITIATED');
})->group('unit');

test('can get transaction URL', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->transactionUrl())->toStartWith('https://sandbox.checkout.tap.company');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get source ID', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->sourceId())->toBe('src_card');
})->group('unit');

test('can get metadata', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->metadata())->toBeArray()
        ->and($authorize->metadata())->toHaveKey('order_id')
        ->and($authorize->metadata()['order_id'])->toBe('ORD-12345');
})->group('unit');

// Status helper tests
test('isPending returns true for INITIATED status', function () {
    $authorize = new Authorize(['status' => 'INITIATED']);

    expect($authorize->isPending())->toBeTrue();
})->group('unit');

test('isAuthorized returns true for AUTHORIZED status', function () {
    $authorize = new Authorize(['status' => 'AUTHORIZED']);

    expect($authorize->isAuthorized())->toBeTrue();
})->group('unit');

test('hasFailed returns true for FAILED status', function () {
    $authorize = new Authorize(['status' => 'FAILED']);

    expect($authorize->hasFailed())->toBeTrue();
})->group('unit');

test('hasFailed returns true for CANCELLED status', function () {
    $authorize = new Authorize(['status' => 'CANCELLED']);

    expect($authorize->hasFailed())->toBeTrue();
})->group('unit');

test('hasFailed returns true for DECLINED status', function () {
    $authorize = new Authorize(['status' => 'DECLINED']);

    expect($authorize->hasFailed())->toBeTrue();
})->group('unit');

test('hasFailed returns true for RESTRICTED status', function () {
    $authorize = new Authorize(['status' => 'RESTRICTED']);

    expect($authorize->hasFailed())->toBeTrue();
})->group('unit');

test('hasFailed returns true for VOID status', function () {
    $authorize = new Authorize(['status' => 'VOID']);

    expect($authorize->hasFailed())->toBeTrue();
})->group('unit');

test('CAPTURED status is neither authorized nor failed nor pending', function () {
    $authorize = new Authorize(['status' => 'CAPTURED']);

    expect($authorize->isAuthorized())->toBeFalse()
        ->and($authorize->hasFailed())->toBeFalse()
        ->and($authorize->isPending())->toBeFalse();
})->group('unit');

test('throws exception for unknown status', function () {
    $authorize = new Authorize(['status' => 'INVALID_STATUS']);

    expect(fn () => $authorize->status())->toThrow(
        TapPay\Tap\Exceptions\InvalidStatusException::class,
        "Unknown authorize status: 'INVALID_STATUS'"
    );
})->group('unit');

test('returns UNKNOWN status when status is missing', function () {
    $authorize = new Authorize([]);

    expect($authorize->status())->toBe(AuthorizeStatus::UNKNOWN)
        ->and($authorize->isAuthorized())->toBeFalse()
        ->and($authorize->hasFailed())->toBeFalse()
        ->and($authorize->isPending())->toBeFalse();
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid authorization ID', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $authorize = new Authorize([]);

    expect($authorize->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without auth prefix', function () {
    $authorize = new Authorize(['id' => 'invalid_id_12345']);

    expect($authorize->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID with wrong prefix', function () {
    $authorize = new Authorize(['id' => 'chg_12345']);

    expect($authorize->hasValidId())->toBeFalse();
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $authorize = new Authorize([]);

    expect($authorize->id())->toBe('');
})->group('unit');

test('throws exception for missing amount', function () {
    $authorize = new Authorize(['currency' => 'SAR']);

    $authorize->amount();
})->throws(TapPay\Tap\Exceptions\InvalidAmountException::class, 'Amount is required but not provided or invalid in response')->group('unit');

test('throws exception for zero amount', function () {
    $authorize = new Authorize(['amount' => 0, 'currency' => 'SAR']);

    $authorize->amount();
})->throws(TapPay\Tap\Exceptions\InvalidAmountException::class, 'Amount must be greater than zero')->group('unit');

test('throws exception for negative amount', function () {
    $authorize = new Authorize(['amount' => -10, 'currency' => 'SAR']);

    $authorize->amount();
})->throws(TapPay\Tap\Exceptions\InvalidAmountException::class, 'Amount must be greater than zero')->group('unit');

test('throws exception for invalid amount', function () {
    $authorize = new Authorize(['amount' => 'invalid', 'currency' => 'SAR']);

    $authorize->amount();
})->throws(TapPay\Tap\Exceptions\InvalidAmountException::class, 'Amount is required but not provided or invalid in response')->group('unit');

test('throws exception for amount when currency is missing', function () {
    config(['tap.currency' => '']);
    $authorize = new Authorize(['amount' => 50]);

    $authorize->amount();
})->throws(TapPay\Tap\Exceptions\InvalidCurrencyException::class, 'Currency is required but not provided in response or config')->group('unit');

test('returns default currency from config when missing', function () {
    config(['tap.currency' => 'USD']);
    $authorize = new Authorize([]);

    expect($authorize->currency())->toBe('USD');
})->group('unit');

test('throws exception for missing currency when config is empty', function () {
    config(['tap.currency' => '']);
    $authorize = new Authorize([]);

    $authorize->currency();
})->throws(TapPay\Tap\Exceptions\InvalidCurrencyException::class, 'Currency is required but not provided in response or config')->group('unit');

test('returns UNKNOWN for missing status', function () {
    $authorize = new Authorize([]);

    expect($authorize->status())->toBe(AuthorizeStatus::UNKNOWN);
})->group('unit');

test('returns null for missing transactionUrl', function () {
    $authorize = new Authorize([]);

    expect($authorize->transactionUrl())->toBeNull();
})->group('unit');

test('returns null for missing customerId', function () {
    $authorize = new Authorize([]);

    expect($authorize->customerId())->toBeNull();
})->group('unit');

test('returns null for missing sourceId', function () {
    $authorize = new Authorize([]);

    expect($authorize->sourceId())->toBeNull();
})->group('unit');

test('returns empty array for missing metadata', function () {
    $authorize = new Authorize([]);

    expect($authorize->metadata())->toBe([]);
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->toArray())->toBeArray()
        ->and($authorize->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $authorize = new Authorize([]);

    expect($authorize->isEmpty())->toBeTrue();
})->group('unit');

test('can check if attribute exists', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->has('id'))->toBeTrue()
        ->and($authorize->has('non_existent'))->toBeFalse();
})->group('unit');

test('can get nested attributes using get method', function () {
    $data = loadFixture('authorize.json');
    $authorize = new Authorize($data);

    expect($authorize->get('customer.first_name'))->toBe('John')
        ->and($authorize->get('customer.email'))->toBe('john.doe@example.com');
})->group('unit');

test('returns default value for non-existent attributes', function () {
    $authorize = new Authorize([]);

    expect($authorize->get('non_existent', 'default'))->toBe('default');
})->group('unit');
