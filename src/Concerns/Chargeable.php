<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;

trait Chargeable
{
    private const CHARGE_OPTIONS = [
        'source',
        'description',
        'metadata',
        'redirect',
        'post',
        'reference',
        'save_card',
        'statement_descriptor',
        'receipt',
        'auto',
    ];

    abstract public function tapCustomerId(): ?string;

    abstract protected function ensureTapCustomerExists(): void;

    protected function money(): MoneyContract
    {
        return app(MoneyContract::class);
    }

    protected function getCurrency(?string $currency = null): string
    {
        if ($currency !== null) {
            return $this->money()->normalizeCurrency($currency);
        }

        return $this->money()->normalizeCurrency(config('tap.currency', 'SAR'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function charge(int $amount, ?string $currency = null, array $options = []): Charge
    {
        $currency = $this->getCurrency($currency);
        $minimumAmount = $this->money()->getMinimumAmount($currency);

        if ($amount < $minimumAmount) {
            throw new InvalidArgumentException(
                "Amount must be at least {$minimumAmount} for {$currency}"
            );
        }

        $this->ensureTapCustomerExists();

        $sanitizedOptions = array_intersect_key($options, array_flip(self::CHARGE_OPTIONS));

        $chargeData = array_merge($sanitizedOptions, [
            'amount' => $this->money()->toDecimal($amount, $currency),
            'currency' => $currency,
            'customer' => ['id' => $this->tapCustomerId()],
        ]);

        return Tap::charges()->create($chargeData);
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function newCharge(int $amount, ?string $currency = null): ChargeBuilder
    {
        $this->ensureTapCustomerExists();

        return (new ChargeBuilder(Tap::charges()))
            ->amount($amount)
            ->currency($this->getCurrency($currency))
            ->customerId($this->tapCustomerId());
    }
}