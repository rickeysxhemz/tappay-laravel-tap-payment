<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Refund;

class RefundService extends AbstractService
{
    /**
     * Get the endpoint for refunds
     */
    protected function getEndpoint(): string
    {
        return 'refunds';
    }

    /**
     * Create a new refund
     */
    public function create(array $data): Refund
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Refund($response);
    }

    /**
     * Retrieve a refund by ID
     */
    public function retrieve(string $refundId): Refund
    {
        $response = $this->client->get($this->getEndpoint() . '/' . $refundId);

        return new Refund($response);
    }

    /**
     * Update a refund
     */
    public function update(string $refundId, array $data): Refund
    {
        $response = $this->client->put($this->getEndpoint() . '/' . $refundId, $data);

        return new Refund($response);
    }

    /**
     * List all refunds
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post($this->getEndpoint() . '/list', $params);

        return array_map(
            fn($refund) => new Refund($refund),
            $response['refunds'] ?? []
        );
    }
}
