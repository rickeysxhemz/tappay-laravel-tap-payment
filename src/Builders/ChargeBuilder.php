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
        $this->data['currency'] = config('tap.currency', 'SAR');
    }

    public function saveCard(bool $save = true): static
    {
        $this->data['save_card'] = $save;

        return $this;
    }

    public function statementDescriptor(string $descriptor): static
    {
        if (strlen($descriptor) > 22) {
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

    #[\Override]
    public function create(): Charge
    {
        $this->validateRequired(['amount', 'source']);

        return $this->service->create($this->toArray());
    }
}