<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use TapPay\Tap\Enums\AgreementType;
use TapPay\Tap\Enums\ContractType;

/**
 * Trait for handling payment agreements
 */
trait HasPaymentAgreement
{
    public function paymentAgreement(
        string $agreementId,
        string|AgreementType $type = AgreementType::UNSCHEDULED
    ): static {
        $existing = $this->getExistingPaymentAgreement();
        $this->data['payment_agreement'] = array_merge($existing, [
            'id' => $agreementId,
            'type' => $type instanceof AgreementType ? $type->value : $type,
        ]);

        return $this;
    }

    public function contract(
        string $contractId,
        string|ContractType $type = ContractType::UNSCHEDULED
    ): static {
        $existing = $this->getExistingPaymentAgreement();
        $this->data['payment_agreement'] = array_merge($existing, [
            'contract' => [
                'id' => $contractId,
                'type' => $type instanceof ContractType ? $type->value : $type,
            ],
        ]);

        return $this;
    }

    public function totalPaymentsCount(int $count): static
    {
        $existing = $this->getExistingPaymentAgreement();
        $this->data['payment_agreement'] = array_merge($existing, ['total_payments_count' => $count]);

        return $this;
    }

    /**
     * Get existing payment agreement data with proper type safety
     *
     * @return array<string, mixed>
     */
    private function getExistingPaymentAgreement(): array
    {
        if (isset($this->data['payment_agreement']) && is_array($this->data['payment_agreement'])) {
            /** @var array<string, mixed> */
            return $this->data['payment_agreement'];
        }

        return [];
    }

    public function customerInitiated(bool $initiated = true): static
    {
        $this->data['customer_initiated'] = $initiated;

        return $this;
    }

    public function merchantInitiated(): static
    {
        return $this->customerInitiated(false);
    }
}
