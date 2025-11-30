<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Token;
use TapPay\Tap\Services\Concerns\HasCreateReadOperations;

/**
 * @extends AbstractService<Token>
 */
class TokenService extends AbstractService
{
    use HasCreateReadOperations;

    protected function getEndpoint(): string
    {
        return 'tokens';
    }

    protected function getListKey(): string
    {
        return 'tokens';
    }

    protected function getResourceClass(): string
    {
        return Token::class;
    }
}