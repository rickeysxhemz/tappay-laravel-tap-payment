<?php

declare(strict_types=1);

use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\ValueObjects\Authentication;
use TapPay\Tap\ValueObjects\Customer;
use TapPay\Tap\ValueObjects\Destination;
use TapPay\Tap\ValueObjects\Money;
use TapPay\Tap\ValueObjects\Phone;
use TapPay\Tap\ValueObjects\Source;

describe('Phone Value Object', function () {
    it('creates phone with country code and number', function () {
        $phone = new Phone('966', '500000000');

        expect($phone->countryCode)->toBe('966')
            ->and($phone->number)->toBe('500000000');
    });

    it('converts to array correctly', function () {
        $phone = new Phone('965', '99999999');

        expect($phone->toArray())->toBe([
            'country_code' => '965',
            'number' => '99999999',
        ]);
    });

    it('can be created with make factory', function () {
        $phone = Phone::make('971', '501234567');

        expect($phone->countryCode)->toBe('971')
            ->and($phone->number)->toBe('501234567');
    });

    it('throws exception for empty country code', function () {
        new Phone('', '500000000');
    })->throws(InvalidArgumentException::class, 'Country code cannot be empty');

    it('throws exception for empty number', function () {
        new Phone('966', '');
    })->throws(InvalidArgumentException::class, 'Phone number cannot be empty');
});

describe('Customer Value Object', function () {
    it('creates customer with all fields', function () {
        $phone = new Phone('966', '500000000');
        $customer = new Customer(
            id: 'cus_123',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: $phone
        );

        expect($customer->id)->toBe('cus_123')
            ->and($customer->firstName)->toBe('John')
            ->and($customer->lastName)->toBe('Doe')
            ->and($customer->email)->toBe('john@example.com')
            ->and($customer->phone)->toBe($phone);
    });

    it('converts to array with all fields', function () {
        $customer = new Customer(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: new Phone('966', '500000000')
        );

        expect($customer->toArray())->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '966',
                'number' => '500000000',
            ],
        ]);
    });

    it('filters null values from array', function () {
        $customer = new Customer(firstName: 'John');

        expect($customer->toArray())->toBe([
            'first_name' => 'John',
        ]);
    });

    it('can be created from ID', function () {
        $customer = Customer::fromId('cus_123');

        expect($customer->id)->toBe('cus_123')
            ->and($customer->firstName)->toBeNull();
    });

    it('can be created with make factory', function () {
        $customer = Customer::make(
            firstName: 'Jane',
            email: 'jane@example.com'
        );

        expect($customer->firstName)->toBe('Jane')
            ->and($customer->email)->toBe('jane@example.com');
    });
});

describe('Destination Value Object', function () {
    it('creates destination with required fields', function () {
        $destination = new Destination('dest_123', 100.50);

        expect($destination->id)->toBe('dest_123')
            ->and($destination->amount)->toBe(100.50)
            ->and($destination->currency)->toBeNull();
    });

    it('creates destination with currency', function () {
        $destination = new Destination('dest_123', 100.50, 'SAR');

        expect($destination->currency)->toBe('SAR');
    });

    it('converts to array without currency', function () {
        $destination = new Destination('dest_123', 50.00);

        expect($destination->toArray())->toBe([
            'id' => 'dest_123',
            'amount' => 50.00,
        ]);
    });

    it('converts to array with currency', function () {
        $destination = new Destination('dest_123', 50.00, 'KWD');

        expect($destination->toArray())->toBe([
            'id' => 'dest_123',
            'amount' => 50.00,
            'currency' => 'KWD',
        ]);
    });

    it('can be created with make factory', function () {
        $destination = Destination::make('dest_456', 200.00, 'USD');

        expect($destination->id)->toBe('dest_456')
            ->and($destination->amount)->toBe(200.00)
            ->and($destination->currency)->toBe('USD');
    });

    it('throws exception for empty ID', function () {
        new Destination('', 100.00);
    })->throws(InvalidArgumentException::class, 'Destination ID cannot be empty');

    it('throws exception for zero amount', function () {
        new Destination('dest_123', 0);
    })->throws(InvalidArgumentException::class, 'Destination amount must be positive');

    it('throws exception for negative amount', function () {
        new Destination('dest_123', -100.00);
    })->throws(InvalidArgumentException::class, 'Destination amount must be positive');
});

describe('Authentication Value Object', function () {
    it('creates authentication with ECI only', function () {
        $auth = new Authentication(eci: '05');

        expect($auth->eci)->toBe('05')
            ->and($auth->cavv)->toBeNull()
            ->and($auth->xid)->toBeNull();
    });

    it('creates authentication with all fields', function () {
        $auth = new Authentication(
            eci: '05',
            cavv: 'AAACAgSRBklmQCFgMpEGAAAAAAA=',
            xid: 'MDAwMDAwMDAwMDAwMDAwMDAwMDE=',
            dsTransId: 'f38e6948-5388-41a6-bca4-b49723c19437',
            version: '2.1.0'
        );

        expect($auth->eci)->toBe('05')
            ->and($auth->cavv)->toBe('AAACAgSRBklmQCFgMpEGAAAAAAA=')
            ->and($auth->xid)->toBe('MDAwMDAwMDAwMDAwMDAwMDAwMDE=')
            ->and($auth->dsTransId)->toBe('f38e6948-5388-41a6-bca4-b49723c19437')
            ->and($auth->version)->toBe('2.1.0');
    });

    it('converts to array filtering null values', function () {
        $auth = new Authentication(eci: '05', cavv: 'test_cavv');

        expect($auth->toArray())->toBe([
            'eci' => '05',
            'cavv' => 'test_cavv',
        ]);
    });

    it('converts to array with ds_trans_id key', function () {
        $auth = new Authentication(eci: '05', dsTransId: 'trans_123');

        expect($auth->toArray())->toBe([
            'eci' => '05',
            'ds_trans_id' => 'trans_123',
        ]);
    });

    it('can create for 3DS version 1', function () {
        $auth = Authentication::forVersion1('05', 'cavv_value', 'xid_value');

        expect($auth->eci)->toBe('05')
            ->and($auth->cavv)->toBe('cavv_value')
            ->and($auth->xid)->toBe('xid_value')
            ->and($auth->version)->toBe('1.0.2');
    });

    it('can create for 3DS version 2', function () {
        $auth = Authentication::forVersion2('05', 'cavv_value', 'ds_trans_id_value');

        expect($auth->eci)->toBe('05')
            ->and($auth->cavv)->toBe('cavv_value')
            ->and($auth->dsTransId)->toBe('ds_trans_id_value')
            ->and($auth->version)->toBe('2.1.0');
    });

    it('throws exception for empty ECI', function () {
        new Authentication(eci: '');
    })->throws(InvalidArgumentException::class, 'ECI (Electronic Commerce Indicator) is required');
});

describe('Money Value Object', function () {
    it('creates money from smallest unit', function () {
        $money = Money::fromSmallestUnit(1000, 'SAR');

        expect($money->amount)->toBe(1000)
            ->and($money->currency)->toBe('SAR');
    });

    it('creates money from decimal', function () {
        $money = Money::fromDecimal(10.50, 'SAR');

        expect($money->amount)->toBe(1050)
            ->and($money->currency)->toBe('SAR');
    });

    it('creates money from decimal for 3-decimal currency', function () {
        $money = Money::fromDecimal(10.500, 'KWD');

        expect($money->amount)->toBe(10500)
            ->and($money->currency)->toBe('KWD');
    });

    it('converts to decimal for 2-decimal currency', function () {
        $money = Money::fromSmallestUnit(1050, 'SAR');

        expect($money->toDecimal())->toBe(10.50);
    });

    it('converts to decimal for 3-decimal currency', function () {
        $money = Money::fromSmallestUnit(10500, 'KWD');

        expect($money->toDecimal())->toBe(10.500);
    });

    it('returns correct decimal places', function () {
        $sar = Money::fromSmallestUnit(100, 'SAR');
        $kwd = Money::fromSmallestUnit(100, 'KWD');

        expect($sar->getDecimalPlaces())->toBe(2)
            ->and($kwd->getDecimalPlaces())->toBe(3);
    });

    it('returns correct minimum amount', function () {
        $sar = Money::fromSmallestUnit(100, 'SAR');
        $kwd = Money::fromSmallestUnit(100, 'KWD');

        expect($sar->getMinimumAmount())->toBe(10)
            ->and($kwd->getMinimumAmount())->toBe(100);
    });

    it('validates minimum amount successfully', function () {
        $money = Money::fromSmallestUnit(10, 'SAR');

        expect($money->validateMinimum())->toBe($money);
    });

    it('throws exception when validating minimum fails', function () {
        $money = Money::fromSmallestUnit(5, 'SAR');
        $money->validateMinimum();
    })->throws(InvalidArgumentException::class, 'Amount must be at least 10 for SAR');

    it('converts to array correctly', function () {
        $money = Money::fromSmallestUnit(1050, 'SAR');

        expect($money->toArray())->toBe([
            'amount' => 10.50,
            'currency' => 'SAR',
        ]);
    });

    it('throws exception for negative amount', function () {
        Money::fromSmallestUnit(-100, 'SAR');
    })->throws(InvalidArgumentException::class, 'Amount cannot be negative');

    it('throws exception for unsupported currency', function () {
        Money::fromSmallestUnit(100, 'XYZ');
    })->throws(\TapPay\Tap\Exceptions\InvalidCurrencyException::class);

    it('normalizes currency to uppercase', function () {
        $money = Money::fromSmallestUnit(100, 'sar');

        expect($money->currency)->toBe('SAR');
    });
});

describe('Source Value Object', function () {
    it('creates source from string', function () {
        $source = Source::make('src_card');

        expect($source->id)->toBe('src_card');
    });

    it('creates source from SourceObject enum', function () {
        $source = Source::make(SourceObject::SRC_CARD);

        expect($source->id)->toBe('src_card');
    });

    it('creates token source with validation', function () {
        $source = Source::fromToken('tok_abc123');

        expect($source->id)->toBe('tok_abc123')
            ->and($source->isToken())->toBeTrue();
    });

    it('throws exception for invalid token format', function () {
        Source::fromToken('invalid_token');
    })->throws(InvalidArgumentException::class, 'Token ID must start with "tok_"');

    it('creates authorization source with validation', function () {
        $source = Source::fromAuthorization('auth_abc123');

        expect($source->id)->toBe('auth_abc123')
            ->and($source->isAuthorizationCapture())->toBeTrue();
    });

    it('throws exception for invalid authorization format', function () {
        Source::fromAuthorization('invalid_auth');
    })->throws(InvalidArgumentException::class, 'Authorization ID must start with "auth_"');

    it('identifies redirect sources', function () {
        $source = Source::make('src_card');

        expect($source->isRedirect())->toBeTrue()
            ->and($source->isToken())->toBeFalse()
            ->and($source->isAuthorizationCapture())->toBeFalse();
    });

    it('identifies token sources', function () {
        $source = Source::fromToken('tok_abc');

        expect($source->isToken())->toBeTrue()
            ->and($source->isRedirect())->toBeFalse();
    });

    it('identifies authorization sources', function () {
        $source = Source::fromAuthorization('auth_abc');

        expect($source->isAuthorizationCapture())->toBeTrue()
            ->and($source->isRedirect())->toBeFalse();
    });

    it('converts to array', function () {
        $source = Source::make(SourceObject::SRC_KNET);

        expect($source->toArray())->toBe(['id' => 'src_kw.knet']);
    });

    it('throws exception for empty source ID', function () {
        new Source('');
    })->throws(InvalidArgumentException::class, 'Source ID cannot be empty');

    it('throws exception for whitespace source ID', function () {
        new Source('   ');
    })->throws(InvalidArgumentException::class, 'Source ID cannot be empty');
});
