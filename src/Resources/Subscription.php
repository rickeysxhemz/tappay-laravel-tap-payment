<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Enums\SubscriptionStatus;
use TapPay\Tap\Resources\Concerns\HasCustomer;
use TapPay\Tap\Resources\Concerns\HasMoney;

class Subscription extends Resource
{
    use HasCustomer;
    use HasMoney;

    protected function getIdPrefix(): string
    {
        return 'sub_';
    }

    public function status(): SubscriptionStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'CANCELLED');

        return SubscriptionStatus::tryFrom($status) ?? SubscriptionStatus::CANCELLED;
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

    public function startDate(): ?Carbon
    {
        $start = $this->attributes['start_date'] ?? $this->attributes['created'] ?? null;

        return $start ? $this->parseDateTime($start) : null;
    }

    public function currentPeriodStart(): ?Carbon
    {
        $start = $this->attributes['current_period_start'] ?? null;

        return $start ? $this->parseDateTime($start) : null;
    }

    public function currentPeriodEnd(): ?Carbon
    {
        $end = $this->attributes['current_period_end'] ?? null;

        return $end ? $this->parseDateTime($end) : null;
    }

    public function cancelledAt(): ?Carbon
    {
        $cancelled = $this->attributes['cancelled_at'] ?? null;

        return $cancelled ? $this->parseDateTime($cancelled) : null;
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
