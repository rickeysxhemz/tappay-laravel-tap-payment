<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use TapPay\Tap\Enums\SourceObject;

/**
 * Payment source value object for Tap API
 *
 * @implements Arrayable<string, string>
 */
readonly class Source implements Arrayable
{
    private const PREFIX_TOKEN = 'tok_';
    private const PREFIX_AUTH = 'auth_';

    public function __construct(
        public string $id
    ) {
        if (empty(trim($this->id))) {
            throw new InvalidArgumentException('Source ID cannot be empty');
        }
    }

    public static function make(string|SourceObject $source): self
    {
        $id = $source instanceof SourceObject ? $source->value : $source;

        return new self($id);
    }

    public static function fromToken(string $tokenId): self
    {
        if (!str_starts_with($tokenId, self::PREFIX_TOKEN)) {
            throw new InvalidArgumentException('Token ID must start with "tok_"');
        }

        return new self($tokenId);
    }

    public static function fromAuthorization(string $authId): self
    {
        if (!str_starts_with($authId, self::PREFIX_AUTH)) {
            throw new InvalidArgumentException('Authorization ID must start with "auth_"');
        }

        return new self($authId);
    }

    public function isToken(): bool
    {
        return str_starts_with($this->id, self::PREFIX_TOKEN);
    }

    public function isAuthorizationCapture(): bool
    {
        return str_starts_with($this->id, self::PREFIX_AUTH);
    }

    public function isRedirect(): bool
    {
        return !$this->isToken() && !$this->isAuthorizationCapture();
    }

    /** @return array{id: string} */
    public function toArray(): array
    {
        return ['id' => $this->id];
    }
}