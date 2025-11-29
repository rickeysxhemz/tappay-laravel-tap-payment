<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'ACTIVE';
    case PAUSED = 'PAUSED';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';
    case PAST_DUE = 'PAST_DUE';
    case TRIALING = 'TRIALING';

    public function isActive(): bool
    {
        return match ($this) {
            self::ACTIVE, self::TRIALING => true,
            default => false,
        };
    }

    public function isPaused(): bool
    {
        return $this === self::PAUSED;
    }

    public function isCancelled(): bool
    {
        return match ($this) {
            self::CANCELLED, self::EXPIRED => true,
            default => false,
        };
    }

    public function requiresAttention(): bool
    {
        return $this === self::PAST_DUE;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::PAST_DUE => 'Past Due',
            self::TRIALING => 'Trialing',
        };
    }
}