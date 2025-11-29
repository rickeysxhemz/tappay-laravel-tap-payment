<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\ValueObjects\Money;

/**
 * @see HasTapCustomer
 */
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
        return $this->money()->normalizeCurrency(
            $currency ?? config('tap.currency', 'SAR')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws ApiErrorException
     */
    protected function getValidCustomerId(): string
    {
        $this->ensureTapCustomerExists();

        $customerId = $this->tapCustomerId();

        if ($customerId === null) {
            throw new InvalidArgumentException('Failed to create or retrieve Tap customer ID');
        }

        return $customerId;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ApiErrorException
     */
    public function charge(int|Money $amount, ?string $currency = null, array $options = []): Charge
    {
        if ($amount instanceof Money) {
            $amount->validateMinimum();
            $decimalAmount = $amount->toDecimal();
            $currency = $amount->currency;
        } else {
            $currency = $this->getCurrency($currency);
            $minimumAmount = $this->money()->getMinimumAmount($currency);

            if ($amount < $minimumAmount) {
                throw new InvalidArgumentException(
                    "Amount must be at least {$minimumAmount} for {$currency}"
                );
            }

            $decimalAmount = $this->money()->toDecimal($amount, $currency);
        }

        $sanitizedOptions = array_intersect_key($options, array_flip(self::CHARGE_OPTIONS));

        $chargeData = array_merge($sanitizedOptions, [
            'amount' => $decimalAmount,
            'currency' => $currency,
            'customer' => ['id' => $this->getValidCustomerId()],
        ]);

        return Tap::charges()->create($chargeData);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ApiErrorException
     */
    public function newCharge(int|Money $amount, ?string $currency = null): ChargeBuilder
    {
        $builder = new ChargeBuilder(Tap::charges(), $this->money());

        if ($amount instanceof Money) {
            $builder->amount($amount);
        } else {
            $builder->amount($amount)->currency($this->getCurrency($currency));
        }

        return $builder->customerId($this->getValidCustomerId());
    }
}
