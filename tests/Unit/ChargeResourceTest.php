<?php

declare(strict_types=1);

use TapPay\Tap\Enums\ChargeStatus;
use TapPay\Tap\Resources\Charge;

test('can create charge resource from array', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge)->toBeInstanceOf(Charge::class);
})->group('unit');

test('can get charge ID', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->id())->toBe('chg_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get charge amount', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->amount())->toBe(10.5);
})->group('unit');

test('can get charge currency', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->currency())->toBe('SAR');
})->group('unit');

test('can get charge status', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->status())->toBeInstanceOf(ChargeStatus::class)
        ->and($charge->status()->value)->toBe('INITIATED');
})->group('unit');

test('can get transaction URL', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->transactionUrl())->toStartWith('https://sandbox.checkout.tap.company');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get description', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->description())->toBe('Test charge');
})->group('unit');

test('can get metadata', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->metadata())->toBeArray()
        ->and($charge->metadata())->toHaveKey('udf1');
})->group('unit');

test('can check if charge is successful', function () {
    $data = loadFixture('charge.json');
    $data['status'] = 'CAPTURED';
    $charge = new Charge($data);

    expect($charge->isSuccessful())->toBeTrue();
})->group('unit');

test('can check if charge is pending', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->isPending())->toBeTrue();
})->group('unit');

test('can check if charge has failed', function () {
    $data = loadFixture('charge.json');
    $data['status'] = 'FAILED';
    $charge = new Charge($data);

    expect($charge->hasFailed())->toBeTrue();
})->group('unit');

test('can convert to array', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->toArray())->toBeArray()
        ->and($charge->toArray())->toBe($data);
})->group('unit');

test('can get nested attributes using get method', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->get('customer.first_name'))->toBe('John')
        ->and($charge->get('customer.email'))->toBe('john.doe@example.com');
})->group('unit');

test('returns default value for non-existent attributes', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->get('non_existent', 'default'))->toBe('default');
})->group('unit');

test('can check if attribute exists', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->has('id'))->toBeTrue()
        ->and($charge->has('non_existent'))->toBeFalse();
})->group('unit');

test('is not empty with data', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->isEmpty())->toBeFalse();
})->group('unit');

test('is empty with no data', function () {
    $charge = new Charge([]);

    expect($charge->isEmpty())->toBeTrue();
})->group('unit');

test('can access attributes via magic getter', function () {
    $data = loadFixture('charge.json');
    $charge = new Charge($data);

    expect($charge->id)->toBe('chg_TS02A2220231616Xm0B1234567')
        ->and($charge->amount)->toBe(10.5);
})->group('unit');
