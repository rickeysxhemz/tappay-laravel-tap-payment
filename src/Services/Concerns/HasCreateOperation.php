<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

/**
 * Provides standard create operation for services
 */
trait HasCreateOperation
{
    /**
     * Create a new resource
     *
     * @param  array  $data  Resource data
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): mixed
    {
        $response = $this->client->post($this->getEndpoint(), $data);
        $resourceClass = $this->getResourceClass();

        return new $resourceClass($response);
    }
}
