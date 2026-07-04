<?php

declare(strict_types=1);

use TapPay\Tap\Builders\AbstractBuilder;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Resources\Resource;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\ValueObjects\Money;

beforeEach(function () {
    $this->money = Mockery::mock(MoneyContract::class);
    $this->money->shouldReceive('normalizeCurrency')->andReturnUsing(fn ($c) => strtoupper($c));
    $this->money->shouldReceive('toDecimal')->andReturnUsing(fn ($a, $c = null) => (float) $a / 100);
    $this->money->shouldReceive('getMinimumAmount')->andReturn(100);

    $this->service = Mockery::mock(ChargeService::class);
    $this->builder = new ChargeBuilder($this->service, $this->money);
});

describe('HasAmount trait', function () {
    it('accepts a Money value object and copies its amount and currency', function () {
        $money = new Money(1000, 'SAR', $this->money);

        $this->builder->amount($money);
        $data = $this->builder->toArray();

        expect($data['amount'])->toBe(10.0)
            ->and($data['currency'])->toBe('SAR');
    });

    it('accepts a Money value object via the money() method', function () {
        $money = new Money(2500, 'SAR', $this->money);

        $this->builder->money($money);

        expect($this->builder->toArray()['amount'])->toBe(25.0);
    });

    it('throws when a negative integer amount is provided', function () {
        expect(fn () => $this->builder->amount(-100))
            ->toThrow(InvalidArgumentException::class, 'Amount cannot be negative');
    });

    it('throws when formatting an amount that was never set', function () {
        $method = new ReflectionMethod($this->builder, 'getFormattedAmount');

        expect(fn () => $method->invoke($this->builder))
            ->toThrow(InvalidArgumentException::class, 'Amount is not set');
    });

    it('skips minimum validation when no amount is set', function () {
        $method = new ReflectionMethod($this->builder, 'validateMinimumAmount');

        expect(fn () => $method->invoke($this->builder))
            ->not->toThrow(InvalidArgumentException::class);
    });

    it('falls back to the configured currency when none is set on the builder', function () {
        config()->set('tap.currency', 'SAR');

        $builder = new class($this->money) extends AbstractBuilder
        {
            public function create(): Resource
            {
                throw new RuntimeException('not used in this test');
            }
        };

        $builder->amount(1000);

        expect($builder->toArray()['amount'])->toBe(10.0);
    });

    it('falls back to SAR when the configured currency is not a string', function () {
        config()->set('tap.currency', 12345);

        $builder = new class($this->money) extends AbstractBuilder
        {
            public function create(): Resource
            {
                throw new RuntimeException('not used in this test');
            }
        };

        $builder->amount(1000);

        expect($builder->toArray()['amount'])->toBe(10.0);
    });
});
