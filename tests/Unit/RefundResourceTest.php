<?php

declare(strict_types=1);

use TapPay\Tap\Enums\RefundStatus;
use TapPay\Tap\Resources\Refund;

test('can create refund resource from array', function () {
    $data = loadFixture('refund.json');
    $refund = new Refund($data);

    expect($refund)->toBeInstanceOf(Refund::class);
})->group('unit');

test('can get refund ID', function () {
    $data = loadFixture('refund.json');
    $refund = new Refund($data);

    expect($refund->id())->toStartWith('ref_');
})->group('unit');

test('can get refund amount', function () {
    $refund = new Refund(['amount' => 50.0]);

    expect($refund->amount())->toBe(50.0);
})->group('unit');

test('can get refund currency', function () {
    $refund = new Refund(['currency' => 'SAR']);

    expect($refund->currency())->toBe('SAR');
})->group('unit');

test('can get refund status', function () {
    $refund = new Refund(['status' => 'SUCCEEDED']);

    expect($refund->status())->toBeInstanceOf(RefundStatus::class)
        ->and($refund->status()->value)->toBe('SUCCEEDED');
})->group('unit');

test('can get charge ID', function () {
    $refund = new Refund(['charge_id' => 'chg_12345']);

    expect($refund->chargeId())->toBe('chg_12345');
})->group('unit');

test('can get refund reason', function () {
    $refund = new Refund(['reason' => 'Customer request']);

    expect($refund->reason())->toBe('Customer request');
})->group('unit');

test('can get refund metadata', function () {
    $refund = new Refund(['metadata' => ['key' => 'value']]);

    expect($refund->metadata())->toBeArray()
        ->and($refund->metadata()['key'])->toBe('value');
})->group('unit');

// Status helper tests
test('isSuccessful returns true for SUCCEEDED status', function () {
    $refund = new Refund(['status' => 'SUCCEEDED']);

    expect($refund->isSuccessful())->toBeTrue();
})->group('unit');

test('isPending returns true for PENDING status', function () {
    $refund = new Refund(['status' => 'PENDING']);

    expect($refund->isPending())->toBeTrue();
})->group('unit');

test('isPending returns true for INITIATED status', function () {
    $refund = new Refund(['status' => 'INITIATED']);

    expect($refund->isPending())->toBeTrue();
})->group('unit');

test('hasFailed returns true for FAILED status', function () {
    $refund = new Refund(['status' => 'FAILED']);

    expect($refund->hasFailed())->toBeTrue();
})->group('unit');

test('hasFailed returns true for CANCELLED status', function () {
    $refund = new Refund(['status' => 'CANCELLED']);

    expect($refund->hasFailed())->toBeTrue();
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid refund ID', function () {
    $refund = new Refund(['id' => 'ref_12345']);

    expect($refund->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $refund = new Refund([]);

    expect($refund->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without ref prefix', function () {
    $refund = new Refund(['id' => 'chg_12345']);

    expect($refund->hasValidId())->toBeFalse();
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $refund = new Refund([]);

    expect($refund->id())->toBe('');
})->group('unit');

test('returns zero for missing amount', function () {
    $refund = new Refund([]);

    expect($refund->amount())->toBe(0.0);
})->group('unit');

test('returns empty string for missing currency', function () {
    $refund = new Refund([]);

    expect($refund->currency())->toBe('');
})->group('unit');

test('returns FAILED for missing status', function () {
    $refund = new Refund([]);

    expect($refund->status())->toBe(RefundStatus::FAILED);
})->group('unit');

test('returns empty string for missing chargeId', function () {
    $refund = new Refund([]);

    expect($refund->chargeId())->toBe('');
})->group('unit');

test('returns null for missing reason', function () {
    $refund = new Refund([]);

    expect($refund->reason())->toBeNull();
})->group('unit');

test('returns empty array for missing metadata', function () {
    $refund = new Refund([]);

    expect($refund->metadata())->toBe([]);
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('refund.json');
    $refund = new Refund($data);

    expect($refund->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $refund = new Refund(['id' => 're_123']);

    expect($refund->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $refund = new Refund([]);

    expect($refund->isEmpty())->toBeTrue();
})->group('unit');
