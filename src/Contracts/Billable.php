<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Resources\Customer;
use TapPay\Tap\Resources\Token;

/**
 * @see \TapPay\Tap\Concerns\Billable
 */
interface Billable
{
    public function tapCustomerId(): ?string;

    public function setTapCustomerId(?string $customerId): void;

    public function createAsTapCustomer(array $options = []): Customer;

    public function asTapCustomer(): ?Customer;

    public function updateTapCustomer(array $data): Customer;

    public function deleteTapCustomer(): void;

    /**
     * @param int $amount Amount in smallest currency unit
     */
    public function charge(int $amount, ?string $currency = null, array $options = []): Charge;

    /**
     * @param int $amount Amount in smallest currency unit
     */
    public function newCharge(int $amount, ?string $currency = null): ChargeBuilder;

    public function createCardToken(string $cardId): Token;
}