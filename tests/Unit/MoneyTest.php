<?php

declare(strict_types=1);

use TapPay\Tap\Support\Money;

beforeEach(function () {
    $this->money = new Money('SAR');
});

describe('Money', function () {
    test('throws exception for unsupported currency in constructor', function () {
        new Money('XYZ');
    })->throws(
        InvalidArgumentException::class,
        "Currency 'XYZ' is not supported"
    )->group('unit');

    test('throws exception for empty currency in constructor', function () {
        new Money('');
    })->throws(
        InvalidArgumentException::class,
        'Currency cannot be empty'
    )->group('unit');

    test('throws exception for empty currency in normalizeCurrency', function () {
        $money = new Money('SAR');
        $money->normalizeCurrency('');
    })->throws(
        InvalidArgumentException::class,
        'Currency cannot be empty'
    )->group('unit');

    test('throws exception for whitespace currency in normalizeCurrency', function () {
        $money = new Money('SAR');
        $money->normalizeCurrency('   ');
    })->throws(
        InvalidArgumentException::class,
        'Currency cannot be empty'
    )->group('unit');

    test('throws exception for unsupported currency in getDecimalPlaces', function () {
        $this->money->getDecimalPlaces('INVALID');
    })->throws(
        InvalidArgumentException::class,
        "Currency 'INVALID' is not supported"
    )->group('unit');

    test('throws exception for unsupported currency in normalizeCurrency', function () {
        $this->money->normalizeCurrency('invalid');
    })->throws(
        InvalidArgumentException::class,
        "Currency 'INVALID' is not supported"
    )->group('unit');

    test('returns correct decimals for 3-decimal currencies', function () {
        expect($this->money->getDecimalPlaces('KWD'))->toBe(3)
            ->and($this->money->getDecimalPlaces('BHD'))->toBe(3)
            ->and($this->money->getDecimalPlaces('OMR'))->toBe(3)
            ->and($this->money->getDecimalPlaces('JOD'))->toBe(3);
    })->group('unit');

    test('returns correct decimals for 2-decimal currencies', function () {
        expect($this->money->getDecimalPlaces('SAR'))->toBe(2)
            ->and($this->money->getDecimalPlaces('AED'))->toBe(2)
            ->and($this->money->getDecimalPlaces('USD'))->toBe(2)
            ->and($this->money->getDecimalPlaces('EUR'))->toBe(2);
    })->group('unit');

    test('converts to decimal correctly for 2-decimal currency', function () {
        expect($this->money->toDecimal(1050, 'SAR'))->toBe(10.5)
            ->and($this->money->toDecimal(100, 'USD'))->toBe(1.0)
            ->and($this->money->toDecimal(1, 'EUR'))->toBe(0.01);
    })->group('unit');

    test('converts to decimal correctly for 3-decimal currency', function () {
        expect($this->money->toDecimal(1050, 'KWD'))->toBe(1.05)
            ->and($this->money->toDecimal(1000, 'BHD'))->toBe(1.0)
            ->and($this->money->toDecimal(1, 'OMR'))->toBe(0.001);
    })->group('unit');

    test('throws exception for negative amount in toDecimal', function () {
        $this->money->toDecimal(-1000, 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount cannot be negative'
    )->group('unit');

    test('converts to smallest unit correctly', function () {
        expect($this->money->toSmallestUnit(10.5, 'SAR'))->toBe(1050)
            ->and($this->money->toSmallestUnit(1.05, 'KWD'))->toBe(1050)
            ->and($this->money->toSmallestUnit('25.99', 'USD'))->toBe(2599);
    })->group('unit');

    test('toSmallestUnit handles integer input', function () {
        expect($this->money->toSmallestUnit(10, 'SAR'))->toBe(1000)
            ->and($this->money->toSmallestUnit(1, 'KWD'))->toBe(1000)
            ->and($this->money->toSmallestUnit(0, 'USD'))->toBe(0);
    })->group('unit');

    test('toSmallestUnit handles zero amount', function () {
        expect($this->money->toSmallestUnit(0, 'SAR'))->toBe(0)
            ->and($this->money->toSmallestUnit(0.0, 'SAR'))->toBe(0)
            ->and($this->money->toSmallestUnit('0', 'SAR'))->toBe(0)
            ->and($this->money->toSmallestUnit('0.00', 'SAR'))->toBe(0);
    })->group('unit');

    test('toSmallestUnit handles very small amounts', function () {
        expect($this->money->toSmallestUnit(0.01, 'SAR'))->toBe(1)
            ->and($this->money->toSmallestUnit(0.001, 'KWD'))->toBe(1)
            ->and($this->money->toSmallestUnit('0.01', 'USD'))->toBe(1);
    })->group('unit');

    test('toSmallestUnit handles string with spaces', function () {
        expect($this->money->toSmallestUnit(' 10.50 ', 'SAR'))->toBe(1050)
            ->and($this->money->toSmallestUnit('  25.99  ', 'USD'))->toBe(2599);
    })->group('unit');

    test('toSmallestUnit handles string with leading zeros', function () {
        expect($this->money->toSmallestUnit('010.50', 'SAR'))->toBe(1050)
            ->and($this->money->toSmallestUnit('00.99', 'USD'))->toBe(99);
    })->group('unit');

    test('toSmallestUnit handles scientific notation', function () {
        expect($this->money->toSmallestUnit('1e2', 'SAR'))->toBe(10000)
            ->and($this->money->toSmallestUnit('1.5e1', 'SAR'))->toBe(1500);
    })->group('unit');

    test('toSmallestUnit uses default currency when not specified', function () {
        $money = new Money('KWD');
        expect($money->toSmallestUnit(1.5))->toBe(1500);

        $money2 = new Money('SAR');
        expect($money2->toSmallestUnit(1.5))->toBe(150);
    })->group('unit');

    test('toSmallestUnit handles floating point precision', function () {
        // 0.1 + 0.2 = 0.30000000000000004 in IEEE 754
        expect($this->money->toSmallestUnit(0.1 + 0.2, 'SAR'))->toBe(30)
            ->and($this->money->toSmallestUnit(19.99, 'SAR'))->toBe(1999);
    })->group('unit');

    test('toSmallestUnit handles large amounts', function () {
        expect($this->money->toSmallestUnit(999999.99, 'SAR'))->toBe(99999999)
            ->and($this->money->toSmallestUnit(1000000, 'USD'))->toBe(100000000);
    })->group('unit');

    test('throws exception for invalid amount in toSmallestUnit', function () {
        $this->money->toSmallestUnit('invalid', 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount must be a valid numeric value'
    )->group('unit');

    test('throws exception for empty string in toSmallestUnit', function () {
        $this->money->toSmallestUnit('', 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount must be a valid numeric value'
    )->group('unit');

    test('throws exception for negative amount in toSmallestUnit', function () {
        $this->money->toSmallestUnit(-100, 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount cannot be negative'
    )->group('unit');

    test('throws exception for negative float in toSmallestUnit', function () {
        $this->money->toSmallestUnit(-10.5, 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount cannot be negative'
    )->group('unit');

    test('throws exception for negative string in toSmallestUnit', function () {
        $this->money->toSmallestUnit('-25.99', 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount cannot be negative'
    )->group('unit');

    test('returns correct minimum amount for each decimal type', function () {
        // 3 decimals: minimum 100 (0.100)
        expect($this->money->getMinimumAmount('KWD'))->toBe(100);
        // 2 decimals: minimum 10 (0.10)
        expect($this->money->getMinimumAmount('SAR'))->toBe(10);
    })->group('unit');

    test('formats amount correctly', function () {
        expect($this->money->format(1050, 'SAR'))->toBe('10.50 SAR')
            ->and($this->money->format(1050, 'KWD'))->toBe('1.050 KWD');
    })->group('unit');

    test('throws exception for negative amount in format', function () {
        $this->money->format(-1050, 'SAR');
    })->throws(
        InvalidArgumentException::class,
        'Amount cannot be negative'
    )->group('unit');

    test('isSupported returns true for valid currencies', function () {
        expect($this->money->isSupported('SAR'))->toBeTrue()
            ->and($this->money->isSupported('kwd'))->toBeTrue()
            ->and($this->money->isSupported('USD'))->toBeTrue();
    })->group('unit');

    test('isSupported returns false for invalid currencies', function () {
        expect($this->money->isSupported('XYZ'))->toBeFalse()
            ->and($this->money->isSupported('FAKE'))->toBeFalse();
    })->group('unit');

    test('normalizes currency to uppercase', function () {
        expect($this->money->normalizeCurrency('sar'))->toBe('SAR')
            ->and($this->money->normalizeCurrency('  kwd  '))->toBe('KWD');
    })->group('unit');

    test('returns all supported currencies', function () {
        $currencies = $this->money->getSupportedCurrencies();

        expect($currencies)->toContain('KWD', 'BHD', 'OMR', 'JOD', 'SAR', 'AED', 'QAR', 'EGP', 'LBP', 'USD', 'EUR', 'GBP')
            ->and(count($currencies))->toBe(12);
    })->group('unit');
});