<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Resources\Charge;

class ChargeService extends AbstractService
{
    /**
     * Get the endpoint for charges
     */
    protected function getEndpoint(): string
    {
        return 'charges';
    }

    /**
     * Create a new charge builder
     */
    public function newBuilder(): ChargeBuilder
    {
        return new ChargeBuilder($this);
    }

    /**
     * Create a new charge
     */
    public function create(array $data): Charge
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Charge($response);
    }

    /**
     * Retrieve a charge by ID
     */
    public function retrieve(string $chargeId): Charge
    {
        $response = $this->client->get($this->getEndpoint() . '/' . $chargeId);

        return new Charge($response);
    }

    /**
     * Update a charge
     */
    public function update(string $chargeId, array $data): Charge
    {
        $response = $this->client->put($this->getEndpoint() . '/' . $chargeId, $data);

        return new Charge($response);
    }

    /**
     * List all charges
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post($this->getEndpoint() . '/list', $params);

        return array_map(
            fn($charge) => new Charge($charge),
            $response['charges'] ?? []
        );
    }
}
