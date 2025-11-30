<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Authorize;
use TapPay\Tap\Services\Concerns\HasStandardOperations;

/**
 * @extends AbstractService<Authorize>
 */
class AuthorizeService extends AbstractService
{
    use HasStandardOperations;

    protected function getEndpoint(): string
    {
        return 'authorize';
    }

    protected function getListKey(): string
    {
        return 'authorizations';
    }

    protected function getResourceClass(): string
    {
        return Authorize::class;
    }
}