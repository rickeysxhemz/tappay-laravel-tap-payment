<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Resource;

trait HasUpdateOperation
{
    /**
     * Update a resource
     *
     * @param  string  $id  Resource ID
     * @param  array<string, mixed>  $data  Update data
     * @return resource
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function update(string $id, array $data): mixed
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $id), $data);
        $resourceClass = $this->getResourceClass();

        return new $resourceClass($response);
    }
}
