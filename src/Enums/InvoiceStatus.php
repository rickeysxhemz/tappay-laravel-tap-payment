<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';
    case FAILED = 'FAILED';

    public function isSuccessful(): bool
    {
        return $this === self::PAID;
    }

    public function isPending(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING], true);
    }

    public function hasFailed(): bool
    {
        return in_array($this, [self::CANCELLED, self::EXPIRED, self::FAILED], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::FAILED => 'Failed',
        };
    }
}