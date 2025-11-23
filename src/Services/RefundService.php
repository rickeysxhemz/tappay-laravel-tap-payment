<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Refund;

class RefundService extends AbstractService
{
    /**
     * Get the endpoint for refunds
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'refunds';
    }

    /**
     * Create a new refund
     *
     * @param array $data Refund data
     * @return Refund
     */
    public function create(array $data): Refund
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Refund($response);
    }

    /**
     * Retrieve a refund by ID
     *
     * @param string $refundId Refund ID
     * @return Refund
     */
    public function retrieve(string $refundId): Refund
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $refundId));

        return new Refund($response);
    }

    /**
     * Update a refund
     *
     * @param string $refundId Refund ID
     * @param array $data Update data
     * @return Refund
     */
    public function update(string $refundId, array $data): Refund
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $refundId), $data);

        return new Refund($response);
    }

    /**
     * List all refunds
     *
     * @param array $params Query parameters
     * @return Refund[]
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return array_map(
            fn($refund) => new Refund($refund),
            $response['refunds'] ?? []
        );
    }
}
