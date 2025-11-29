<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * 3DS Authentication value object for external authentication results
 *
 * @implements Arrayable<string, string>
 */
readonly class Authentication implements Arrayable
{
    public function __construct(
        public string $eci,
        public ?string $cavv = null,
        public ?string $xid = null,
        public ?string $dsTransId = null,
        public ?string $version = null
    ) {
        if (empty($this->eci)) {
            throw new InvalidArgumentException('ECI (Electronic Commerce Indicator) is required');
        }
    }

    /**
     * Create authentication for 3DS 1.0
     */
    public static function forVersion1(string $eci, ?string $cavv = null, ?string $xid = null): self
    {
        return new self(
            eci: $eci,
            cavv: $cavv,
            xid: $xid,
            version: '1.0.2'
        );
    }

    /**
     * Create authentication for 3DS 2.0
     */
    public static function forVersion2(
        string $eci,
        ?string $cavv = null,
        ?string $dsTransId = null,
        string $version = '2.1.0'
    ): self {
        return new self(
            eci: $eci,
            cavv: $cavv,
            dsTransId: $dsTransId,
            version: $version
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'eci' => $this->eci,
            'cavv' => $this->cavv,
            'xid' => $this->xid,
            'ds_trans_id' => $this->dsTransId,
            'version' => $this->version,
        ], static fn ($value): bool => $value !== null);
    }
}