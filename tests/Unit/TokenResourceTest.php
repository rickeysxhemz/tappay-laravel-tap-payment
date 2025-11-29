<?php

declare(strict_types=1);

use TapPay\Tap\Resources\Token;

test('can create token resource from array', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token)->toBeInstanceOf(Token::class);
})->group('unit');

test('can get token ID', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->id())->toBe('tok_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get card ID', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->cardId())->toBe('card_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get created timestamp', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->created())->toBe(1616439916);
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid token ID', function () {
    $token = new Token(['id' => 'tok_12345']);

    expect($token->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $token = new Token([]);

    expect($token->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without tok prefix', function () {
    $token = new Token(['id' => 'card_12345']);

    expect($token->hasValidId())->toBeFalse();
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $token = new Token([]);

    expect($token->id())->toBe('');
})->group('unit');

test('returns null for missing cardId', function () {
    $token = new Token([]);

    expect($token->cardId())->toBeNull();
})->group('unit');

test('returns null for missing customerId', function () {
    $token = new Token([]);

    expect($token->customerId())->toBeNull();
})->group('unit');

test('returns null for missing created', function () {
    $token = new Token([]);

    expect($token->created())->toBeNull();
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $token = new Token(['id' => 'tok_123']);

    expect($token->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $token = new Token([]);

    expect($token->isEmpty())->toBeTrue();
})->group('unit');

test('can check if attribute exists', function () {
    $data = loadFixture('token.json');
    $token = new Token($data);

    expect($token->has('id'))->toBeTrue()
        ->and($token->has('non_existent'))->toBeFalse();
})->group('unit');
