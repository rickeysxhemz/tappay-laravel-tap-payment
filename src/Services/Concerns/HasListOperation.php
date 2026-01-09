<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

trait HasListOperation
{
    /**
     * List all resources
     *
     * @param  array<string, mixed>  $params  Query parameters
     * @return array<\TapPay\Tap\Resources\Resource>
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function list(array $params = []): array
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return $this->mapToResources($response);
    }
}
