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

    test('can enable 3D Secure', function () {
        $this->builder->threeDSecure();

        expect($this->builder->get('threeDSecure'))->toBeTrue();
    })->group('unit');

    test('can disable 3D Secure', function () {
        $this->builder->threeDSecure(false);

        expect($this->builder->get('threeDSecure'))->toBeFalse();
    })->group('unit');

    test('can set customer initiated flag', function () {
        $this->builder->customerInitiated();

        expect($this->builder->get('customer_initiated'))->toBeTrue();
    })->group('unit');

    test('can set merchant initiated flag', function () {
        $this->builder->customerInitiated(false);

        expect($this->builder->get('customer_initiated'))->toBeFalse();
    })->group('unit');

    test('can set merchant ID', function () {
        $this->builder->merchant('mer_123456');

        expect($this->builder->get('merchant'))->toBe(['id' => 'mer_123456']);
    })->group('unit');

    test('can set payment agreement', function () {
        $this->builder->paymentAgreement('pa_123456');

        expect($this->builder->get('payment_agreement'))->toBe([
            'id' => 'pa_123456',
            'type' => 'UNSCHEDULED',
        ]);
    })->group('unit');

    test('can set payment agreement with custom type', function () {
        $this->builder->paymentAgreement('pa_123456', 'RECURRING');

        expect($this->builder->get('payment_agreement'))->toBe([
            'id' => 'pa_123456',
            'type' => 'RECURRING',
        ]);
    })->group('unit');

    test('can set transaction expiry', function () {
        $this->builder->expiresIn(30);

        expect($this->builder->get('transaction'))->toBe([
            'expiry' => [
                'period' => 30,
                'type' => 'MINUTE',
            ],
        ]);
    })->group('unit');

    test('can set destinations for marketplace', function () {
        $destinations = [
            ['id' => 'dest_1', 'amount' => 50.0],
            ['id' => 'dest_2', 'amount' => 30.0],
        ];

        $this->builder->destinations($destinations);

        expect($this->builder->get('destinations'))->toBe(['destination' => $destinations]);
    })->group('unit');

    test('can set order reference', function () {
        $this->builder->orderReference('order_12345');

        expect($this->builder->get('reference'))->toBe(['order' => 'order_12345']);
    })->group('unit');

    test('can set both transaction and order reference', function () {
        $this->builder->reference('txn_12345')->orderReference('order_12345');

        expect($this->builder->get('reference'))->toBe([
            'transaction' => 'txn_12345',
            'order' => 'order_12345',
        ]);
    })->group('unit');

    test('can build merchant initiated transaction', function () {
        $this->builder
            ->amount(5000)
            ->withToken('tok_saved_card')
            ->customerInitiated(false)
            ->threeDSecure(false)
            ->paymentAgreement('pa_123456');

        $array = $this->builder->toArray();

        expect($array['customer_initiated'])->toBeFalse()
            ->and($array['threeDSecure'])->toBeFalse()
            ->and($array['payment_agreement'])->toBe([
                'id' => 'pa_123456',
                'type' => 'UNSCHEDULED',
            ]);
    })->group('unit');

    test('can set authentication object', function () {
        $auth = [
            'eci' => '05',
            'cavv' => 'AAABBJJJkkkAAABBBJJJkkk=',
            'xid' => 'MDAwMDAwMDAwMTE=',
        ];

        $this->builder->authentication($auth);

        expect($this->builder->get('authentication'))->toBe($auth);
    })->group('unit');

    test('can set authentication details with all parameters', function () {
        $this->builder->authenticationDetails(
            eci: '05',
            cavv: 'AAABBJJJkkkAAABBBJJJkkk=',
            xid: 'MDAwMDAwMDAwMTE=',
            dsTransId: 'f25084f0-5b16-4c0a-ae5d-b24808a95e4b',
            version: '2.1.0'
        );

        expect($this->builder->get('authentication'))->toBe([
            'eci' => '05',
            'cavv' => 'AAABBJJJkkkAAABBBJJJkkk=',
            'xid' => 'MDAwMDAwMDAwMTE=',
            'ds_trans_id' => 'f25084f0-5b16-4c0a-ae5d-b24808a95e4b',
            'version' => '2.1.0',
        ]);
    })->group('unit');

    test('can set authentication details with minimal parameters', function () {
        $this->builder->authenticationDetails(eci: '07');

        expect($this->builder->get('authentication'))->toBe(['eci' => '07']);
    })->group('unit');

    test('can set contract for payment agreement', function () {
        $this->builder->contract('card_123456');

        expect($this->builder->get('payment_agreement'))->toBe([
            'contract' => [
                'id' => 'card_123456',
                'type' => 'UNSCHEDULED',
            ],
        ]);
    })->group('unit');

    test('can set contract with custom type', function () {
        $this->builder->contract('sub_123456', 'RECURRING');

        expect($this->builder->get('payment_agreement'))->toBe([
            'contract' => [
                'id' => 'sub_123456',
                'type' => 'RECURRING',
            ],
        ]);
    })->group('unit');

    test('can set total payments count', function () {
        $this->builder->totalPaymentsCount(12);

        expect($this->builder->get('payment_agreement'))->toBe([
            'total_payments_count' => 12,
        ]);
    })->group('unit');

    test('can combine payment agreement with contract and count', function () {
        $this->builder
            ->paymentAgreement('pa_123456', 'RECURRING')
            ->contract('card_789', 'RECURRING')
            ->totalPaymentsCount(12);

        expect($this->builder->get('payment_agreement'))->toBe([
            'id' => 'pa_123456',
            'type' => 'RECURRING',
            'contract' => [
                'id' => 'card_789',
                'type' => 'RECURRING',
            ],
            'total_payments_count' => 12,
        ]);
    })->group('unit');

    test('can build charge with external 3DS authentication', function () {
        $this->builder
            ->amount(5000)
            ->withToken('tok_google_pay')
            ->authenticationDetails(
                eci: '05',
                cavv: 'AAABBJJJkkkAAABBBJJJkkk=',
                version: '2.2.0'
            );

        $array = $this->builder->toArray();

        expect($array['authentication'])->toBe([
            'eci' => '05',
            'cavv' => 'AAABBJJJkkkAAABBBJJJkkk=',
            'version' => '2.2.0',
        ]);
    })->group('unit');

    test('can set platform ID', function () {
        $this->builder->platform('woocommerce_v1');

        expect($this->builder->get('platform'))->toBe(['id' => 'woocommerce_v1']);
    })->group('unit');

    test('can build complete charge with all parameters', function () {
        $this->builder
            ->amount(10000)
            ->currency('SAR')
            ->withCard()
            ->customer([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => ['country_code' => '966', 'number' => '500000000'],
            ])
            ->description('Complete test charge')
            ->redirectUrl('https://example.com/callback')
            ->postUrl('https://example.com/webhook')
            ->reference('txn_123')
            ->orderReference('order_456')
            ->metadata(['key' => 'value'])
            ->saveCard()
            ->emailReceipt()
            ->smsReceipt()
            ->statementDescriptor('ACME STORE')
            ->threeDSecure()
            ->customerInitiated()
            ->merchant('mer_123')
            ->platform('laravel_tap_v1')
            ->expiresIn(30);

        $array = $this->builder->toArray();

        expect($array)
            ->toHaveKey('amount')
            ->toHaveKey('currency')
            ->toHaveKey('source')
            ->toHaveKey('customer')
            ->toHaveKey('description')
            ->toHaveKey('redirect')
            ->toHaveKey('post')
            ->toHaveKey('reference')
            ->toHaveKey('metadata')
            ->toHaveKey('save_card')
            ->toHaveKey('receipt')
            ->toHaveKey('statement_descriptor')
            ->toHaveKey('threeDSecure')
            ->toHaveKey('customer_initiated')
            ->toHaveKey('merchant')
            ->toHaveKey('platform')
            ->toHaveKey('transaction');
    })->group('unit');
});
