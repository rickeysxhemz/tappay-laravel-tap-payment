<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Resources\Charge;

class ChargeService extends AbstractService
{
    /**
     * Get the endpoint for charges
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'charges';
    }

    /**
     * Create a new charge builder
     *
     * @return ChargeBuilder
     */
    public function newBuilder(): ChargeBuilder
    {
        return new ChargeBuilder($this);
    }

    /**
     * Create a new charge
     *
     * @param array $data Charge data
     * @return Charge
     */
    public function create(array $data): Charge
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Charge($response);
    }

    /**
     * Retrieve a charge by ID
     *
     * @param string $chargeId Charge ID
     * @return Charge
     */
    public function retrieve(string $chargeId): Charge
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $chargeId));

        return new Charge($response);
    }

    /**
     * Update a charge
     *
     * @param string $chargeId Charge ID
     * @param array $data Update data
     * @return Charge
     */
    public function update(string $chargeId, array $data): Charge
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $chargeId), $data);

        return new Charge($response);
    }

    /**
     * List all charges
     *
     * @param array $params Query parameters
     * @return Charge[]
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return array_map(
            fn($charge) => new Charge($charge),
            $response['charges'] ?? []
        );
    }
}
