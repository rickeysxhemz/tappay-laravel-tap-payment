<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Services\Concerns\HasStandardOperations;

/**
 * @extends AbstractService<Charge>
 */
class ChargeService extends AbstractService
{
    use HasStandardOperations;

    public function __construct(
        Client $client,
        protected MoneyContract $money
    ) {
        parent::__construct($client);
    }

    protected function getEndpoint(): string
    {
        return 'charges';
    }

    protected function getListKey(): string
    {
        return 'charges';
    }

    protected function getResourceClass(): string
    {
        return Charge::class;
    }

    /**
     * Create a new charge builder
     */
    public function newBuilder(): ChargeBuilder
    {
        return new ChargeBuilder($this, $this->money);
    }
}
