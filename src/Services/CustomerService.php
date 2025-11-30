<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Customer;
use TapPay\Tap\Services\Concerns\HasCrudOperations;

/**
 * @extends AbstractService<Customer>
 */
class CustomerService extends AbstractService
{
    use HasCrudOperations;

    protected function getEndpoint(): string
    {
        return 'customers';
    }

    protected function getListKey(): string
    {
        return 'customers';
    }

    protected function getResourceClass(): string
    {
        return Customer::class;
    }
}