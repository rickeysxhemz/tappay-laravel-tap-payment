<?php

declare(strict_types=1);

use TapPay\Tap\Enums\AgreementType;
use TapPay\Tap\Enums\ContractType;

describe('AgreementType Enum', function () {
    it('has UNSCHEDULED case', function () {
        expect(AgreementType::UNSCHEDULED->value)->toBe('UNSCHEDULED');
    });

    it('has RECURRING case', function () {
        expect(AgreementType::RECURRING->value)->toBe('RECURRING');
    });

    it('has INSTALLMENT case', function () {
        expect(AgreementType::INSTALLMENT->value)->toBe('INSTALLMENT');
    });

    it('can be created from string value', function () {
        $type = AgreementType::from('RECURRING');
        expect($type)->toBe(AgreementType::RECURRING);
    });

    it('returns all cases', function () {
        expect(AgreementType::cases())->toHaveCount(3);
    });
});

describe('ContractType Enum', function () {
    it('has UNSCHEDULED case', function () {
        expect(ContractType::UNSCHEDULED->value)->toBe('UNSCHEDULED');
    });

    it('has RECURRING case', function () {
        expect(ContractType::RECURRING->value)->toBe('RECURRING');
    });

    it('has INSTALLMENT case', function () {
        expect(ContractType::INSTALLMENT->value)->toBe('INSTALLMENT');
    });

    it('can be created from string value', function () {
        $type = ContractType::from('INSTALLMENT');
        expect($type)->toBe(ContractType::INSTALLMENT);
    });

    it('returns all cases', function () {
        expect(ContractType::cases())->toHaveCount(3);
    });
});
