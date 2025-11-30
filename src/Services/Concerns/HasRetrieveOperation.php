<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

/**
 * Provides standard retrieve operation for services
 */
trait HasRetrieveOperation
{
    /**
     * Retrieve a resource by ID
     *
     * @param  string  $id  Resource ID
     * @return \TapPay\Tap\Resources\Resource
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If resource ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $id): mixed
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $id));
        $resourceClass = $this->getResourceClass();

        return new $resourceClass($response);
    }
}
