<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Merchant;
use TapPay\Tap\Services\Concerns\HasCrudOperations;

/**
 * Service for managing marketplace sub-merchants
 *
 * @extends AbstractService<Merchant>
 */
class MerchantService extends AbstractService
{
    use HasCrudOperations;

    protected function getEndpoint(): string
    {
        return 'merchants';
    }

    protected function getListKey(): string
    {
        return 'merchants';
    }

    protected function getResourceClass(): string
    {
        return Merchant::class;
    }
}