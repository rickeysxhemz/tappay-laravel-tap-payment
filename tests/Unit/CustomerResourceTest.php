<?php

declare(strict_types=1);

use TapPay\Tap\Resources\Customer;

test('can create customer resource from array', function () {
    $data = loadFixture('customer.json');
    $customer = new Customer($data);

    expect($customer)->toBeInstanceOf(Customer::class);
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('customer.json');
    $customer = new Customer($data);

    expect($customer->id())->toStartWith('cus_');
})->group('unit');

test('can get customer first name', function () {
    $data = loadFixture('customer.json');
    $customer = new Customer($data);

    expect($customer->firstName())->toBeString();
})->group('unit');

test('can get customer last name', function () {
    $customer = new Customer(['last_name' => 'Doe']);

    expect($customer->lastName())->toBe('Doe');
})->group('unit');

test('can get customer email', function () {
    $customer = new Customer(['email' => 'john@example.com']);

    expect($customer->email())->toBe('john@example.com');
})->group('unit');

test('can get customer phone', function () {
    $customer = new Customer(['phone' => ['country_code' => '966', 'number' => '500000000']]);

    expect($customer->phone())->toBeArray()
        ->and($customer->phone()['country_code'])->toBe('966');
})->group('unit');

test('can get customer metadata', function () {
    $customer = new Customer(['metadata' => ['key' => 'value']]);

    expect($customer->metadata())->toBeArray()
        ->and($customer->metadata()['key'])->toBe('value');
})->group('unit');

test('fullName returns combined first and last name', function () {
    $customer = new Customer(['first_name' => 'John', 'last_name' => 'Doe']);

    expect($customer->fullName())->toBe('John Doe');
})->group('unit');

test('fullName returns only first name when last name is null', function () {
    $customer = new Customer(['first_name' => 'John']);

    expect($customer->fullName())->toBe('John');
})->group('unit');

test('fullName returns empty string when no names', function () {
    $customer = new Customer([]);

    expect($customer->fullName())->toBe('');
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid customer ID', function () {
    $customer = new Customer(['id' => 'cus_12345']);

    expect($customer->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $customer = new Customer([]);

    expect($customer->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without cus prefix', function () {
    $customer = new Customer(['id' => 'invalid_12345']);

    expect($customer->hasValidId())->toBeFalse();
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $customer = new Customer([]);

    expect($customer->id())->toBe('');
})->group('unit');

test('returns empty string for missing firstName', function () {
    $customer = new Customer([]);

    expect($customer->firstName())->toBe('');
})->group('unit');

test('returns null for missing lastName', function () {
    $customer = new Customer([]);

    expect($customer->lastName())->toBeNull();
})->group('unit');

test('returns null for missing email', function () {
    $customer = new Customer([]);

    expect($customer->email())->toBeNull();
})->group('unit');

test('returns null for missing phone', function () {
    $customer = new Customer([]);

    expect($customer->phone())->toBeNull();
})->group('unit');

test('returns empty array for missing metadata', function () {
    $customer = new Customer([]);

    expect($customer->metadata())->toBe([]);
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = ['id' => 'cus_123', 'first_name' => 'John'];
    $customer = new Customer($data);

    expect($customer->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $customer = new Customer(['id' => 'cus_123']);

    expect($customer->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $customer = new Customer([]);

    expect($customer->isEmpty())->toBeTrue();
})->group('unit');