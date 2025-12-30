<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use TapPay\Tap\Builders\Concerns\HasChargeCapabilities;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Services\ChargeService;

/**
 * Fluent builder for creating Tap Charges
 */
class ChargeBuilder extends AbstractBuilder
{
    use HasChargeCapabilities;

    public function __construct(
        protected readonly ChargeService $service,
        MoneyContract $money
    ) {
        parent::__construct($money);
        $currency = config('tap.currency', 'SAR');
        $this->data['currency'] = is_string($currency) ? $currency : 'SAR';
    }

    public function saveCard(bool $save = true): static
    {
        $this->data['save_card'] = $save;

        return $this;
    }

    public function statementDescriptor(string $descriptor): static
    {
        if (mb_strlen($descriptor, 'UTF-8') > 22) {
            throw new \InvalidArgumentException('Statement descriptor must be 22 characters or less');
        }

        $this->data['statement_descriptor'] = $descriptor;

        return $this;
    }

    public function auto(array $auto): static
    {
        $this->data['auto'] = $auto;

        return $this;
    }

    public function merchant(string $merchantId): static
    {
        $this->data['merchant'] = ['id' => $merchantId];

        return $this;
    }

    public function expiresIn(int $minutes): static
    {
        if ($minutes < 1 || $minutes > 43200) {
            throw new \InvalidArgumentException('Expiry must be between 1 and 43200 minutes (30 days)');
        }

        $this->data['transaction'] = [
            'expiry' => [
                'period' => $minutes,
                'type' => 'MINUTE',
            ],
        ];

        return $this;
    }

    public function platform(string $platformId): static
    {
        $this->data['platform'] = ['id' => $platformId];

        return $this;
    }

    /**
     * Mark as customer-initiated transaction (for saved cards)
     */
    public function customerInitiated(bool $initiated = true): static
    {
        $this->data['customer_initiated'] = $initiated;

        return $this;
    }

    /**
     * Set transaction expiry time
     *
     * @param  int  $minutes  Minutes until expiry (5-60)
     */
    public function transactionExpiry(int $minutes): static
    {
        if ($minutes < 5 || $minutes > 60) {
            throw new \InvalidArgumentException('Transaction expiry must be between 5 and 60 minutes');
        }

        /** @var array<string, mixed> $existingTransaction */
        $existingTransaction = $this->data['transaction'] ?? [];
        $this->data['transaction'] = array_merge(
            $existingTransaction,
            [
                'expiry' => [
                    'period' => $minutes,
                    'type' => 'MINUTE',
                ],
            ]
        );

        return $this;
    }

    #[\Override]
    public function create(): Charge
    {
        $this->validateRequired(['amount', 'source']);

        /** @var Charge */
        return $this->service->create($this->toArray());
    }
}
