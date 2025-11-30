<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Refund;
use TapPay\Tap\Services\Concerns\HasStandardOperations;

/**
 * @extends AbstractService<Refund>
 */
class RefundService extends AbstractService
{
    use HasStandardOperations;

    protected function getEndpoint(): string
    {
        return 'refunds';
    }

    protected function getListKey(): string
    {
        return 'refunds';
    }

    protected function getResourceClass(): string
    {
        return Refund::class;
    }
}