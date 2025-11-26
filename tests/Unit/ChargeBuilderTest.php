<?php

declare(strict_types=1);

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\Services\ChargeService;

beforeEach(function () {
    $this->service = Mockery::mock(ChargeService::class);
    $this->money = app(MoneyContract::class);
    $this->builder = new ChargeBuilder($this->service, $this->money);
});

afterEach(function () {
    Mockery::close();
});

describe('ChargeBuilder', function () {
    test('can set amount', function () {
        $this->builder->amount(10050);

        expect($this->builder->get('amount'))->toBe(100.5);
    })->group('unit');

    test('throws exception for invalid amount', function () {
        $this->builder->amount(5)->toArray();
    })->throws(InvalidArgumentException::class, 'Amount must be at least 10 for SAR')
      ->group('unit');

    test('can set currency', function () {
        $this->builder->currency('KWD');

        expect($this->builder->get('currency'))->toBe('KWD');
    })->group('unit');

    test('can set source with string', function () {
        $this->builder->source('src_card');

        expect($this->builder->get('source'))->toBe(['id' => 'src_card']);
    })->group('unit');

    test('can set source with enum', function () {
        $this->builder->source(SourceObject::SRC_KNET);

        expect($this->builder->get('source'))->toBe(['id' => 'src_kw.knet']);
    })->group('unit');

    test('can set card payment method', function () {
        $this->builder->withCard();

        expect($this->builder->get('source'))->toBe(['id' => 'src_card']);
    })->group('unit');

    test('can set MADA payment method', function () {
        $this->builder->withMADA();

        expect($this->builder->get('source'))->toBe(['id' => 'src_sa.mada']);
    })->group('unit');

    test('can set KNET payment method', function () {
        $this->builder->withKNET();

        expect($this->builder->get('source'))->toBe(['id' => 'src_kw.knet']);
    })->group('unit');

    test('can set token', function () {
        $this->builder->withToken('tok_test_123');

        expect($this->builder->get('source'))->toBe(['id' => 'tok_test_123']);
    })->group('unit');

    test('throws exception for invalid token format', function () {
        $this->builder->withToken('invalid_token');
    })->throws(InvalidArgumentException::class, 'Token ID must start with "tok_"')
      ->group('unit');

    test('can set authorization to capture', function () {
        $this->builder->captureAuthorization('auth_test_123');

        expect($this->builder->get('source'))->toBe(['id' => 'auth_test_123']);
    })->group('unit');

    test('throws exception for invalid authorization format', function () {
        $this->builder->captureAuthorization('invalid_auth');
    })->throws(InvalidArgumentException::class, 'Authorization ID must start with "auth_"')
      ->group('unit');

    test('can set customer data', function () {
        $customer = [
            'first_name' => 'John',
            'email' => 'john@example.com',
        ];

        $this->builder->customer($customer);

        expect($this->builder->get('customer'))->toBe($customer);
    })->group('unit');

    test('can set customer ID', function () {
        $this->builder->customerId('cus_test_123');

        expect($this->builder->get('customer'))->toBe(['id' => 'cus_test_123']);
    })->group('unit');

    test('can set description', function () {
        $this->builder->description('Test payment');

        expect($this->builder->get('description'))->toBe('Test payment');
    })->group('unit');

    test('can set metadata', function () {
        $metadata = ['order_id' => '12345', 'user_id' => '67890'];

        $this->builder->metadata($metadata);

        expect($this->builder->get('metadata'))->toBe($metadata);
    })->group('unit');

    test('can add single metadata item', function () {
        $this->builder->addMetadata('order_id', '12345');
        $this->builder->addMetadata('user_id', '67890');

        expect($this->builder->get('metadata'))->toBe([
            'order_id' => '12345',
            'user_id' => '67890',
        ]);
    })->group('unit');

    test('can set redirect URL', function () {
        $this->builder->redirectUrl('https://example.com/success');

        expect($this->builder->get('redirect'))->toBe(['url' => 'https://example.com/success']);
    })->group('unit');

    test('can set post URL', function () {
        $this->builder->postUrl('https://example.com/webhook');

        expect($this->builder->get('post'))->toBe(['url' => 'https://example.com/webhook']);
    })->group('unit');

    test('can set reference', function () {
        $this->builder->reference('order_12345');

        expect($this->builder->get('reference'))->toBe(['transaction' => 'order_12345']);
    })->group('unit');

    test('can enable save card', function () {
        $this->builder->saveCard();

        expect($this->builder->get('save_card'))->toBeTrue();
    })->group('unit');

    test('can disable save card', function () {
        $this->builder->saveCard(false);

        expect($this->builder->get('save_card'))->toBeFalse();
    })->group('unit');

    test('can set statement descriptor', function () {
        $this->builder->statementDescriptor('ACME STORE');

        expect($this->builder->get('statement_descriptor'))->toBe('ACME STORE');
    })->group('unit');

    test('can enable email receipt', function () {
        $this->builder->emailReceipt();

        expect($this->builder->get('receipt'))->toBe(['email' => true]);
    })->group('unit');

    test('can enable SMS receipt', function () {
        $this->builder->smsReceipt();

        expect($this->builder->get('receipt'))->toBe(['sms' => true]);
    })->group('unit');

    test('can chain multiple methods', function () {
        $this->builder
            ->amount(5000)
            ->currency('KWD')
            ->withCard()
            ->description('Chained test')
            ->saveCard()
            ->metadata(['test' => 'value']);

        // KWD has 3 decimal places: 5000 / 1000 = 5.0
        expect($this->builder->get('amount'))->toBe(5.0)
            ->and($this->builder->get('currency'))->toBe('KWD')
            ->and($this->builder->get('source'))->toBe(['id' => 'src_card'])
            ->and($this->builder->get('description'))->toBe('Chained test')
            ->and($this->builder->get('save_card'))->toBeTrue()
            ->and($this->builder->get('metadata'))->toBe(['test' => 'value']);
    })->group('unit');

    test('can convert to array', function () {
        $this->builder
            ->amount(2550)
            ->currency('SAR')
            ->withCard();

        $array = $this->builder->toArray();

        expect($array)->toBeArray()
            ->and($array['amount'])->toBe(25.5)
            ->and($array['currency'])->toBe('SAR')
            ->and($array['source'])->toBe(['id' => 'src_card']);
    })->group('unit');

    test('has check returns true for existing keys', function () {
        $this->builder->amount(1000);

        expect($this->builder->has('amount'))->toBeTrue();
    })->group('unit');

    test('has check returns false for non-existing keys', function () {
        expect($this->builder->has('non_existent'))->toBeFalse();
    })->group('unit');

    test('can reset builder data', function () {
        $this->builder
            ->amount(1000)
            ->currency('SAR')
            ->withCard();

        $this->builder->reset();

        expect($this->builder->toArray())->toBeEmpty();
    })->group('unit');

    test('has default currency from config', function () {
        expect($this->builder->get('currency'))->toBe('SAR');
    })->group('unit');
});
