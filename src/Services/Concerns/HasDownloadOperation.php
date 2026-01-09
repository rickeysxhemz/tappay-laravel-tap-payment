<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

trait HasDownloadOperation
{
    /**
     * Download/export resources with optional filters
     *
     * @param  array<string, mixed>  $filters  Filter parameters
     * @return array<string, mixed> Download response data
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function download(array $filters = []): array
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/download', $this->getEndpoint()), $filters);

        return $response;
    }
}
