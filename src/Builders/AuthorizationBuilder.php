<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use InvalidArgumentException;
use TapPay\Tap\Builders\Concerns\HasChargeCapabilities;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Resources\Authorize;
use TapPay\Tap\Services\AuthorizeService;

/**
 * Fluent builder for creating Tap Authorizations
 */
class AuthorizationBuilder extends AbstractBuilder
{
    use HasChargeCapabilities;

    public function __construct(
        protected readonly AuthorizeService $service,
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
            throw new InvalidArgumentException('Statement descriptor must be 22 characters or less');
        }

        $this->data['statement_descriptor'] = $descriptor;

        return $this;
    }

    /**
     * Set auto-capture behavior
     *
     * @param  int  $hours  Hours until auto-capture (1-168)
     */
    public function autoCapture(int $hours): static
    {
        if ($hours < 1 || $hours > 168) {
            throw new InvalidArgumentException('Auto-capture hours must be between 1 and 168');
        }

        $this->data['auto'] = [
            'type' => 'AUTO',
            'time' => $hours,
        ];

        return $this;
    }

    /**
     * Set auto-void behavior
     *
     * @param  int  $hours  Hours until auto-void (1-168)
     */
    public function autoVoid(int $hours): static
    {
        if ($hours < 1 || $hours > 168) {
            throw new InvalidArgumentException('Auto-void hours must be between 1 and 168');
        }

        $this->data['auto'] = [
            'type' => 'VOID',
            'time' => $hours,
        ];

        return $this;
    }

    public function merchant(string $merchantId): static
    {
        $this->data['merchant'] = ['id' => $merchantId];

        return $this;
    }

    #[\Override]
    public function create(): Authorize
    {
        $this->validateRequired(['amount', 'source']);

        /** @var Authorize */
        return $this->service->create($this->toArray());
    }
}
