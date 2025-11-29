<?php

declare(strict_types=1);

use TapPay\Tap\Resources\Card;

test('can create card resource from array', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card)->toBeInstanceOf(Card::class);
})->group('unit');

test('can get card ID', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->id())->toBe('card_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get card object type', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->object())->toBe('card');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get card brand', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->brand())->toBe('VISA');
})->group('unit');

test('can get card funding type', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->funding())->toBe('credit');
})->group('unit');

test('can get first six digits', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->firstSix())->toBe('411111');
})->group('unit');

test('can get last four digits', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->lastFour())->toBe('1111');
})->group('unit');

test('can get expiry month', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->expiryMonth())->toBe(12);
})->group('unit');

test('can get expiry year', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->expiryYear())->toBe(2028);
})->group('unit');

test('can get cardholder name', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->name())->toBe('John Doe');
})->group('unit');

test('can get card fingerprint', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->fingerprint())->toBe('fp_1234567890abcdef');
})->group('unit');

// hasExpiry tests
test('hasExpiry returns true for valid expiry data', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->hasExpiry())->toBeTrue();
})->group('unit');

test('hasExpiry returns false when year is zero', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => 0]);

    expect($card->hasExpiry())->toBeFalse();
})->group('unit');

test('hasExpiry returns false when month is zero', function () {
    $card = new Card(['exp_month' => 0, 'exp_year' => 2028]);

    expect($card->hasExpiry())->toBeFalse();
})->group('unit');

test('hasExpiry returns false when month is invalid', function () {
    $card = new Card(['exp_month' => 13, 'exp_year' => 2028]);

    expect($card->hasExpiry())->toBeFalse();
})->group('unit');

test('hasExpiry returns false when month is negative', function () {
    $card = new Card(['exp_month' => -1, 'exp_year' => 2028]);

    expect($card->hasExpiry())->toBeFalse();
})->group('unit');

test('hasExpiry returns false when year is negative', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => -1]);

    expect($card->hasExpiry())->toBeFalse();
})->group('unit');

// isExpired tests
test('isExpired returns false for future expiry date', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => 2099]);

    expect($card->isExpired())->toBeFalse();
})->group('unit');

test('isExpired returns true for past expiry date', function () {
    $card = new Card(['exp_month' => 1, 'exp_year' => 2020]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns true when year is zero', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => 0]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns true when month is zero', function () {
    $card = new Card(['exp_month' => 0, 'exp_year' => 2028]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns true when month is greater than 12', function () {
    $card = new Card(['exp_month' => 13, 'exp_year' => 2028]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns true when month is negative', function () {
    $card = new Card(['exp_month' => -1, 'exp_year' => 2028]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired returns true when year is negative', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => -1]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

test('isExpired handles 2-digit year format', function () {
    $card = new Card(['exp_month' => 12, 'exp_year' => 99]);

    expect($card->isExpired())->toBeFalse();
})->group('unit');

test('isExpired handles 2-digit year for past date', function () {
    $card = new Card(['exp_month' => 1, 'exp_year' => 20]);

    expect($card->isExpired())->toBeTrue();
})->group('unit');

// maskedNumber tests
test('can get masked card number', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->maskedNumber())->toBe('411111******1111');
})->group('unit');

test('maskedNumber returns empty when firstSix is empty', function () {
    $card = new Card(['first_six' => '', 'last_four' => '1111']);

    expect($card->maskedNumber())->toBe('');
})->group('unit');

test('maskedNumber returns empty when lastFour is empty', function () {
    $card = new Card(['first_six' => '411111', 'last_four' => '']);

    expect($card->maskedNumber())->toBe('');
})->group('unit');

test('maskedNumber returns empty when both are empty', function () {
    $card = new Card([]);

    expect($card->maskedNumber())->toBe('');
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid card ID', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $card = new Card([]);

    expect($card->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without card prefix', function () {
    $card = new Card(['id' => 'invalid_id_12345']);

    expect($card->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID with wrong prefix', function () {
    $card = new Card(['id' => 'chg_12345']);

    expect($card->hasValidId())->toBeFalse();
})->group('unit');

// hasValidCardNumber tests
test('hasValidCardNumber returns true for valid card data', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->hasValidCardNumber())->toBeTrue();
})->group('unit');

test('hasValidCardNumber returns false when firstSix is not 6 digits', function () {
    $card = new Card(['first_six' => '41111', 'last_four' => '1111']);

    expect($card->hasValidCardNumber())->toBeFalse();
})->group('unit');

test('hasValidCardNumber returns false when lastFour is not 4 digits', function () {
    $card = new Card(['first_six' => '411111', 'last_four' => '111']);

    expect($card->hasValidCardNumber())->toBeFalse();
})->group('unit');

test('hasValidCardNumber returns false when firstSix contains non-digits', function () {
    $card = new Card(['first_six' => '41111a', 'last_four' => '1111']);

    expect($card->hasValidCardNumber())->toBeFalse();
})->group('unit');

test('hasValidCardNumber returns false when lastFour contains non-digits', function () {
    $card = new Card(['first_six' => '411111', 'last_four' => '111a']);

    expect($card->hasValidCardNumber())->toBeFalse();
})->group('unit');

test('hasValidCardNumber returns false when both are empty', function () {
    $card = new Card([]);

    expect($card->hasValidCardNumber())->toBeFalse();
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $card = new Card([]);

    expect($card->id())->toBe('');
})->group('unit');

test('returns card for missing object', function () {
    $card = new Card([]);

    expect($card->object())->toBe('card');
})->group('unit');

test('returns empty string for missing customer', function () {
    $card = new Card([]);

    expect($card->customerId())->toBe('');
})->group('unit');

test('returns empty string for missing brand', function () {
    $card = new Card([]);

    expect($card->brand())->toBe('');
})->group('unit');

test('returns empty string for missing funding', function () {
    $card = new Card([]);

    expect($card->funding())->toBe('');
})->group('unit');

test('returns empty string for missing firstSix', function () {
    $card = new Card([]);

    expect($card->firstSix())->toBe('');
})->group('unit');

test('returns empty string for missing lastFour', function () {
    $card = new Card([]);

    expect($card->lastFour())->toBe('');
})->group('unit');

test('returns zero for missing expiryMonth', function () {
    $card = new Card([]);

    expect($card->expiryMonth())->toBe(0);
})->group('unit');

test('returns zero for missing expiryYear', function () {
    $card = new Card([]);

    expect($card->expiryYear())->toBe(0);
})->group('unit');

test('returns empty string for missing name', function () {
    $card = new Card([]);

    expect($card->name())->toBe('');
})->group('unit');

test('returns empty string for missing fingerprint', function () {
    $card = new Card([]);

    expect($card->fingerprint())->toBe('');
})->group('unit');

// toArray and inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->toArray())->toBeArray()
        ->and($card->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $card = new Card([]);

    expect($card->isEmpty())->toBeTrue();
})->group('unit');

test('can check if attribute exists', function () {
    $data = loadFixture('card.json');
    $card = new Card($data);

    expect($card->has('id'))->toBeTrue()
        ->and($card->has('non_existent'))->toBeFalse();
})->group('unit');

test('can get attribute with default', function () {
    $card = new Card([]);

    expect($card->get('non_existent', 'default'))->toBe('default');
})->group('unit');
