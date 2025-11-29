<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Token;

/**
 * Trait for managing payment methods (cards, tokens) via Tap Payments.
 *
 * This trait requires the HasTapCustomer trait to be used on the same class.
 *
 * @see HasTapCustomer
 */
trait HasPaymentMethods
{
    abstract public function tapCustomerId(): ?string;

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function createCardToken(string $cardId): Token
    {
        if (! $this->tapCustomerId()) {
            throw new InvalidArgumentException('Customer must be created in Tap first');
        }

        return Tap::tokens()->create([
            'card' => $cardId,
            'customer' => $this->tapCustomerId(),
        ]);
    }
}
