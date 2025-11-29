<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Http\Client;
use TapPay\Tap\Resources\Resource;

abstract class AbstractService
{
    /**
     * Create a new service instance
     *
     * @param Client $client HTTP client for API communication
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Get the endpoint for this service
     */
    abstract protected function getEndpoint(): string;

    /**
     * Get the response key for list operations (e.g., 'charges', 'refunds')
     */
    abstract protected function getListKey(): string;

    /**
     * Get the resource class for this service
     *
     * @return class-string<Resource>
     */
    abstract protected function getResourceClass(): string;

    /**
     * Map response items to resource instances
     *
     * @param array $response API response
     * @return Resource[]
     */
    protected function mapToResources(array $response): array
    {
        $resourceClass = $this->getResourceClass();
        $items = $response[$this->getListKey()] ?? $response['data'] ?? [];

        return array_map(
            fn(array $item) => new $resourceClass($item),
            $items
        );
    }
}
