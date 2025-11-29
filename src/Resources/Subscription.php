<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;
use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Enums\SubscriptionStatus;

class Subscription extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'sub_';
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
        return $this->get('customer.id') ?? $this->attributes['customer_id'] ?? null;
    }

    public function interval(): ?SubscriptionInterval
    {
        $interval = strtoupper($this->get('term.interval', ''));

        return $interval ? SubscriptionInterval::tryFrom($interval) : null;
    }

    public function period(): int
    {
        return (int) $this->get('term.period', 1);
    }

    public function trialDays(): int
    {
        return (int) $this->get('trial.days', 0);
    }

    public function startDate(): ?DateTime
    {
        $start = $this->attributes['start_date'] ?? $this->attributes['created'] ?? null;

        if (! $start) {
            return null;
        }

        return $this->parseDateTime($start);
    }

    public function currentPeriodStart(): ?DateTime
    {
        $start = $this->attributes['current_period_start'] ?? null;

        if (! $start) {
            return null;
        }

        return $this->parseDateTime($start);
    }

    public function currentPeriodEnd(): ?DateTime
    {
        $end = $this->attributes['current_period_end'] ?? null;

        if (! $end) {
            return null;
        }

        return $this->parseDateTime($end);
    }

    public function cancelledAt(): ?DateTime
    {
        $cancelled = $this->attributes['cancelled_at'] ?? null;

        if (! $cancelled) {
            return null;
        }

        return $this->parseDateTime($cancelled);
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
