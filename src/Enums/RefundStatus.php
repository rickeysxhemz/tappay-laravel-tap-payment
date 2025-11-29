<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum RefundStatus: string
{
    case INITIATED = 'INITIATED';
    case PENDING = 'PENDING';
    case SUCCEEDED = 'SUCCEEDED';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';

    /**
     * Check if the refund is successful
     */
    public function isSuccessful(): bool
    {
        return match ($this) {
            self::SUCCEEDED => true,
            default => false,
        };
    }

    /**
     * Check if the refund is pending
     */
    public function isPending(): bool
    {
        return match ($this) {
            self::INITIATED, self::PENDING => true,
            default => false,
        };
    }

    /**
     * Check if the refund has failed
     */
    public function hasFailed(): bool
    {
        return match ($this) {
            self::FAILED, self::CANCELLED => true,
            default => false,
        };
    }

    /**
     * Get human-readable status label
     */
    public function label(): string
    {
        return match ($this) {
            self::INITIATED => 'Initiated',
            self::PENDING => 'Pending',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
