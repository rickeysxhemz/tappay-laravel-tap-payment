<?php

declare(strict_types=1);

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Services\ChargeService;

beforeEach(function () {
    $this->money = Mockery::mock(MoneyContract::class);
    $this->money->shouldReceive('normalizeCurrency')->andReturnUsing(fn ($c) => strtoupper($c));
    $this->money->shouldReceive('toDecimal')->andReturnUsing(fn ($a, $c) => $a / 100);
    $this->money->shouldReceive('getMinimumAmount')->andReturn(100);

    $this->service = Mockery::mock(ChargeService::class);
    $this->builder = new ChargeBuilder($this->service, $this->money);
});

describe('URL Validation', function () {
    it('accepts valid redirect URL', function () {
        $this->builder->redirectUrl('https://example.com/callback');
        $data = $this->builder->toArray();

        expect($data['redirect']['url'])->toBe('https://example.com/callback');
    });

    it('accepts valid post URL', function () {
        $this->builder->postUrl('https://example.com/webhook');
        $data = $this->builder->toArray();

        expect($data['post']['url'])->toBe('https://example.com/webhook');
    });

    it('rejects invalid redirect URL', function () {
        $this->builder->redirectUrl('not-a-valid-url');
    })->throws(InvalidArgumentException::class, 'Invalid redirect URL format');

    it('rejects invalid post URL', function () {
        $this->builder->postUrl('invalid-webhook');
    })->throws(InvalidArgumentException::class, 'Invalid webhook URL format');

    it('rejects redirect URL without protocol', function () {
        $this->builder->redirectUrl('example.com/callback');
    })->throws(InvalidArgumentException::class, 'Invalid redirect URL format');

    it('accepts http URL', function () {
        $this->builder->redirectUrl('http://localhost:8000/callback');
        $data = $this->builder->toArray();

        expect($data['redirect']['url'])->toBe('http://localhost:8000/callback');
    });
});

describe('Email Validation', function () {
    it('accepts valid email', function () {
        $this->builder->customerEmail('john@example.com');
        $data = $this->builder->toArray();

        expect($data['customer']['email'])->toBe('john@example.com');
    });

    it('rejects invalid email format', function () {
        $this->builder->customerEmail('not-an-email');
    })->throws(InvalidArgumentException::class, 'Invalid email format');

    it('rejects email without domain', function () {
        $this->builder->customerEmail('john@');
    })->throws(InvalidArgumentException::class, 'Invalid email format');

    it('rejects email without at symbol', function () {
        $this->builder->customerEmail('john.example.com');
    })->throws(InvalidArgumentException::class, 'Invalid email format');
});

describe('Phone Validation', function () {
    it('accepts valid phone', function () {
        $this->builder->customerPhone('966', '500000000');
        $data = $this->builder->toArray();

        expect($data['customer']['phone']['country_code'])->toBe('966')
            ->and($data['customer']['phone']['number'])->toBe('500000000');
    });

    it('accepts single digit country code', function () {
        $this->builder->customerPhone('1', '5551234567');
        $data = $this->builder->toArray();

        expect($data['customer']['phone']['country_code'])->toBe('1');
    });

    it('rejects country code with letters', function () {
        $this->builder->customerPhone('96a', '500000000');
    })->throws(InvalidArgumentException::class, 'Country code must be 1-4 digits');

    it('rejects country code longer than 4 digits', function () {
        $this->builder->customerPhone('96612', '500000000');
    })->throws(InvalidArgumentException::class, 'Country code must be 1-4 digits');

    it('rejects empty country code', function () {
        $this->builder->customerPhone('', '500000000');
    })->throws(InvalidArgumentException::class, 'Country code must be 1-4 digits');

    it('rejects phone number too short', function () {
        $this->builder->customerPhone('966', '12345');
    })->throws(InvalidArgumentException::class, 'Phone number must be 6-15 digits');

    it('rejects phone number too long', function () {
        $this->builder->customerPhone('966', '1234567890123456');
    })->throws(InvalidArgumentException::class, 'Phone number must be 6-15 digits');

    it('rejects phone number with letters', function () {
        $this->builder->customerPhone('966', '50000abc0');
    })->throws(InvalidArgumentException::class, 'Phone number must be 6-15 digits');
});

describe('Statement Descriptor Validation', function () {
    it('accepts valid descriptor', function () {
        $this->builder->statementDescriptor('My Store');
        $data = $this->builder->toArray();

        expect($data['statement_descriptor'])->toBe('My Store');
    });

    it('accepts descriptor at max length', function () {
        $descriptor = str_repeat('A', 22);
        $this->builder->statementDescriptor($descriptor);
        $data = $this->builder->toArray();

        expect($data['statement_descriptor'])->toBe($descriptor);
    });

    it('rejects descriptor over 22 characters', function () {
        $this->builder->statementDescriptor('This descriptor is way too long');
    })->throws(InvalidArgumentException::class, 'Statement descriptor must be 22 characters or less');
});

describe('Expiry Validation', function () {
    it('accepts valid expiry', function () {
        $this->builder->expiresIn(60);
        $data = $this->builder->toArray();

        expect($data['transaction']['expiry']['period'])->toBe(60);
    });

    it('accepts minimum expiry of 1 minute', function () {
        $this->builder->expiresIn(1);
        $data = $this->builder->toArray();

        expect($data['transaction']['expiry']['period'])->toBe(1);
    });

    it('accepts maximum expiry of 43200 minutes', function () {
        $this->builder->expiresIn(43200);
        $data = $this->builder->toArray();

        expect($data['transaction']['expiry']['period'])->toBe(43200);
    });

    it('rejects zero expiry', function () {
        $this->builder->expiresIn(0);
    })->throws(InvalidArgumentException::class, 'Expiry must be between 1 and 43200 minutes');

    it('rejects negative expiry', function () {
        $this->builder->expiresIn(-5);
    })->throws(InvalidArgumentException::class, 'Expiry must be between 1 and 43200 minutes');

    it('rejects expiry over 30 days', function () {
        $this->builder->expiresIn(43201);
    })->throws(InvalidArgumentException::class, 'Expiry must be between 1 and 43200 minutes');
});