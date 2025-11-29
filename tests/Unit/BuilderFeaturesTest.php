<?php

declare(strict_types=1);

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Enums\AgreementType;
use TapPay\Tap\Enums\ContractType;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\ValueObjects\Authentication;
use TapPay\Tap\ValueObjects\Customer;
use TapPay\Tap\ValueObjects\Destination;
use TapPay\Tap\ValueObjects\Phone;

beforeEach(function () {
    $this->money = Mockery::mock(MoneyContract::class);
    $this->money->shouldReceive('normalizeCurrency')->andReturnUsing(fn ($c) => strtoupper($c));
    $this->money->shouldReceive('toDecimal')->andReturnUsing(fn ($a, $c) => $a / 100);
    $this->money->shouldReceive('getMinimumAmount')->andReturn(100);

    $this->service = Mockery::mock(ChargeService::class);
    $this->builder = new ChargeBuilder($this->service, $this->money);
});

describe('Conditionable Support', function () {
    it('supports when() for conditional method chaining', function () {
        $shouldSaveCard = true;
        $shouldAddMetadata = false;

        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->when($shouldSaveCard, fn ($b) => $b->saveCard())
            ->when($shouldAddMetadata, fn ($b) => $b->metadata(['key' => 'value']));

        $data = $this->builder->toArray();

        expect($data['save_card'])->toBeTrue()
            ->and($data)->not->toHaveKey('metadata');
    });

    it('supports unless() for inverse conditional', function () {
        $isGuest = false;

        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->unless($isGuest, fn ($b) => $b->customerId('cus_123'));

        $data = $this->builder->toArray();

        expect($data['customer']['id'])->toBe('cus_123');
    });

    it('supports when() with default callback', function () {
        $value = null;

        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->when(
                $value,
                fn ($b, $v) => $b->metadata(['value' => $v]),
                fn ($b) => $b->metadata(['default' => true])
            );

        $data = $this->builder->toArray();

        expect($data['metadata']['default'])->toBeTrue();
    });
});

describe('Value Object Support', function () {
    it('accepts Customer value object', function () {
        $customer = new Customer(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: new Phone('966', '500000000')
        );

        $this->builder->amount(1000)->source('src_card')->customer($customer);
        $data = $this->builder->toArray();

        expect($data['customer'])->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '966',
                'number' => '500000000',
            ],
        ]);
    });

    it('accepts Authentication value object', function () {
        $auth = new Authentication(
            eci: '05',
            cavv: 'test_cavv',
            version: '2.1.0'
        );

        $this->builder->amount(1000)->source('src_card')->authentication($auth);
        $data = $this->builder->toArray();

        expect($data['authentication'])->toBe([
            'eci' => '05',
            'cavv' => 'test_cavv',
            'version' => '2.1.0',
        ]);
    });

    it('accepts Destination value objects array', function () {
        $destinations = [
            new Destination('dest_1', 50.00, 'SAR'),
            new Destination('dest_2', 30.00),
        ];

        $this->builder->amount(1000)->source('src_card')->destinations($destinations);
        $data = $this->builder->toArray();

        expect($data['destinations']['destination'])->toHaveCount(2)
            ->and($data['destinations']['destination'][0])->toBe([
                'id' => 'dest_1',
                'amount' => 50.00,
                'currency' => 'SAR',
            ]);
    });

});

describe('Enum Support', function () {
    it('accepts AgreementType enum', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->paymentAgreement('agr_123', AgreementType::RECURRING);

        $data = $this->builder->toArray();

        expect($data['payment_agreement']['type'])->toBe('RECURRING');
    });

    it('accepts ContractType enum', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->contract('card_123', ContractType::INSTALLMENT);

        $data = $this->builder->toArray();

        expect($data['payment_agreement']['contract']['type'])->toBe('INSTALLMENT');
    });

    it('still accepts string for backward compatibility', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->paymentAgreement('agr_123', 'UNSCHEDULED');

        $data = $this->builder->toArray();

        expect($data['payment_agreement']['type'])->toBe('UNSCHEDULED');
    });
});

describe('New Trait Methods', function () {
    it('supports merchantInitiated shorthand', function () {
        $this->builder->amount(1000)->source('src_card')->merchantInitiated();
        $data = $this->builder->toArray();

        expect($data['customer_initiated'])->toBeFalse();
    });

    it('supports withoutThreeDSecure shorthand', function () {
        $this->builder->amount(1000)->source('src_card')->withoutThreeDSecure();
        $data = $this->builder->toArray();

        expect($data['threeDSecure'])->toBeFalse();
    });

    it('supports withReceipts shorthand', function () {
        $this->builder->amount(1000)->source('src_card')->withReceipts();
        $data = $this->builder->toArray();

        expect($data['receipt']['email'])->toBeTrue()
            ->and($data['receipt']['sms'])->toBeTrue();
    });

    it('supports customerFirstName method', function () {
        $this->builder->amount(1000)->source('src_card')->customerFirstName('John');
        $data = $this->builder->toArray();

        expect($data['customer']['first_name'])->toBe('John');
    });

    it('supports customerLastName method', function () {
        $this->builder->amount(1000)->source('src_card')->customerLastName('Doe');
        $data = $this->builder->toArray();

        expect($data['customer']['last_name'])->toBe('Doe');
    });

    it('supports customerEmail method', function () {
        $this->builder->amount(1000)->source('src_card')->customerEmail('john@example.com');
        $data = $this->builder->toArray();

        expect($data['customer']['email'])->toBe('john@example.com');
    });

    it('supports customerPhone method', function () {
        $this->builder->amount(1000)->source('src_card')->customerPhone('966', '500000000');
        $data = $this->builder->toArray();

        expect($data['customer']['phone'])->toBe([
            'country_code' => '966',
            'number' => '500000000',
        ]);
    });

    it('supports withKFAST method', function () {
        $this->builder->amount(1000)->withKFAST();
        $data = $this->builder->toArray();

        expect($data['source']['id'])->toBe('src_kw.kfast');
    });

    it('supports withFawry method', function () {
        $this->builder->amount(1000)->withFawry();
        $data = $this->builder->toArray();

        expect($data['source']['id'])->toBe('src_eg.fawry');
    });

    it('supports withSTCPay method', function () {
        $this->builder->amount(1000)->withSTCPay();
        $data = $this->builder->toArray();

        expect($data['source']['id'])->toBe('src_stcpay');
    });

    it('supports withTabby method', function () {
        $this->builder->amount(1000)->withTabby();
        $data = $this->builder->toArray();

        expect($data['source']['id'])->toBe('src_tabby');
    });
});

describe('Jsonable Interface', function () {
    it('implements toJson method', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->currency('SAR');

        $json = $this->builder->toJson();
        $decoded = json_decode($json, true);

        expect($decoded['amount'])->toEqual(10.0)
            ->and($decoded['source']['id'])->toBe('src_card')
            ->and($decoded['currency'])->toBe('SAR');
    });

    it('supports JSON encoding options', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card')
            ->metadata(['key' => 'value']);

        $json = $this->builder->toJson(JSON_PRETTY_PRINT);

        expect($json)->toContain("\n");
    });
});

describe('Arrayable Interface', function () {
    it('implements toArray method', function () {
        $this->builder
            ->amount(1000)
            ->source('src_card');

        $array = $this->builder->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKeys(['amount', 'source', 'currency']);
    });
});

describe('Static Return Type', function () {
    it('returns static from fluent methods for proper chaining', function () {
        $result = $this->builder
            ->amount(1000)
            ->currency('SAR')
            ->source('src_card')
            ->customer(['first_name' => 'John'])
            ->metadata(['key' => 'value'])
            ->redirectUrl('https://example.com')
            ->postUrl('https://example.com/webhook')
            ->description('Test charge')
            ->reference('ref_123');

        expect($result)->toBeInstanceOf(ChargeBuilder::class);
    });
});

describe('Readonly Properties', function () {
    it('has readonly service property', function () {
        $reflection = new ReflectionProperty(ChargeBuilder::class, 'service');

        expect($reflection->isReadOnly())->toBeTrue();
    });
});