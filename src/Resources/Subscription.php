<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Enums\SubscriptionStatus;

class Subscription extends Resource
{
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    public function amount(): float
    {
        return (float) ($this->attributes['amount'] ?? 0);
    }

    public function currency(): string
    {
        return $this->attributes['currency'] ?? '';
    }

    public function status(): SubscriptionStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'CANCELLED');
        return SubscriptionStatus::tryFrom($status) ?? SubscriptionStatus::CANCELLED;
    }

    public function customerId(): ?string
    {
        return $this->attributes['customer']['id'] ?? $this->attributes['customer_id'] ?? null;
    }

    public function interval(): ?SubscriptionInterval
    {
        $interval = strtoupper($this->attributes['term']['interval'] ?? '');
        return SubscriptionInterval::tryFrom($interval);
    }

    public function period(): int
    {
        return (int) ($this->attributes['term']['period'] ?? 1);
    }

    public function trialDays(): int
    {
        return (int) ($this->attributes['trial']['days'] ?? 0);
    }

    public function startDate(): ?\DateTime
    {
        $start = $this->attributes['start_date'] ?? $this->attributes['created'] ?? null;

        if (!$start) {
            return null;
        }

        return is_numeric($start) ? (new \DateTime())->setTimestamp($start) : new \DateTime($start);
    }

    public function currentPeriodStart(): ?\DateTime
    {
        $start = $this->attributes['current_period_start'] ?? null;

        if (!$start) {
            return null;
        }

        return is_numeric($start) ? (new \DateTime())->setTimestamp($start) : new \DateTime($start);
    }

    public function currentPeriodEnd(): ?\DateTime
    {
        $end = $this->attributes['current_period_end'] ?? null;

        if (!$end) {
            return null;
        }

        return is_numeric($end) ? (new \DateTime())->setTimestamp($end) : new \DateTime($end);
    }

    public function cancelledAt(): ?\DateTime
    {
        $cancelled = $this->attributes['cancelled_at'] ?? null;

        if (!$cancelled) {
            return null;
        }

        return is_numeric($cancelled) ? (new \DateTime())->setTimestamp($cancelled) : new \DateTime($cancelled);
    }

    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    public function isActive(): bool
    {
        return $this->status()->isActive();
    }

    public function isPaused(): bool
    {
        return $this->status()->isPaused();
    }

    public function isCancelled(): bool
    {
        return $this->status()->isCancelled();
    }

    public function isTrialing(): bool
    {
        return $this->status() === SubscriptionStatus::TRIALING;
    }

    public function onTrial(): bool
    {
        return $this->isTrialing();
    }

    public function requiresAttention(): bool
    {
        return $this->status()->requiresAttention();
    }
}