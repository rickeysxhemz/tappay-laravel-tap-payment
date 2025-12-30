<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Authorize;

/**
 * Provides void operation for authorization services
 *
 * @example
 * // Void an authorization
 * $authorization = $authorizeService->void('auth_abc123');
 *
 * @method string getEndpoint()
 * @method string getResourceClass()
 */
trait HasVoidOperation
{
    /**
     * Void an authorization to release the held funds
     *
     * @param  string  $id  Authorization ID (must start with 'auth_')
     * @return Authorize The voided authorization
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If authorization ID is invalid or already captured
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function void(string $id): Authorize
    {
        if (! str_starts_with($id, 'auth_')) {
            throw new InvalidRequestException('Authorization ID must start with "auth_"');
        }

        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/%s/void', $this->getEndpoint(), $id));

        return new Authorize($response);
    }
}
