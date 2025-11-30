<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

/**
 * Provides standard list operation for services
 */
trait HasListOperation
{
    /**
     * List all resources
     *
     * @param  array  $params  Query parameters
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return $this->mapToResources($response);
    }
}