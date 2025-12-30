<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Builders\AuthorizationBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Resources\Authorize;
use TapPay\Tap\Services\Concerns\HasDownloadOperation;
use TapPay\Tap\Services\Concerns\HasStandardOperations;
use TapPay\Tap\Services\Concerns\HasVoidOperation;

/**
 * @extends AbstractService<Authorize>
 */
class AuthorizeService extends AbstractService
{
    use HasDownloadOperation;
    use HasStandardOperations;
    use HasVoidOperation;

    public function __construct(
        Client $client,
        protected MoneyContract $money
    ) {
        parent::__construct($client);
    }

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

    public function newBuilder(): AuthorizationBuilder
    {
        return new AuthorizationBuilder($this, $this->money);
    }
}
