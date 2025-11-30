<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Enums\SubscriptionStatus;
use TapPay\Tap\Resources\Concerns\HasCustomer;
use TapPay\Tap\Resources\Concerns\HasMoney;

use function is_int;
use function is_numeric;
use function is_string;

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
        $status = $this->getString('status', 'CANCELLED');

        return SubscriptionStatus::tryFrom(strtoupper($status)) ?? SubscriptionStatus::CANCELLED;
    }

    public function interval(): ?SubscriptionInterval
    {
        $interval = $this->get('term.interval', '');

        if (! is_string($interval) || $interval === '') {
            return null;
        }

        return SubscriptionInterval::tryFrom(strtoupper($interval));
    }

    public function period(): int
    {
        $period = $this->get('term.period', 1);

        return is_numeric($period) ? (int) $period : 1;
    }

    public function trialDays(): int
    {
        $days = $this->get('trial.days', 0);

        return is_numeric($days) ? (int) $days : 0;
    }

    public function startDate(): ?Carbon
    {
        $start = $this->attributes['start_date'] ?? $this->attributes['created'] ?? null;

        if ($start === null) {
            return null;
        }

        return is_string($start) || is_int($start) ? $this->parseDateTime($start) : null;
    }

    public function currentPeriodStart(): ?Carbon
    {
        $start = $this->attributes['current_period_start'] ?? null;

        if ($start === null) {
            return null;
        }

        return is_string($start) || is_int($start) ? $this->parseDateTime($start) : null;
    }

    public function currentPeriodEnd(): ?Carbon
    {
        $end = $this->attributes['current_period_end'] ?? null;

        if ($end === null) {
            return null;
        }

        return is_string($end) || is_int($end) ? $this->parseDateTime($end) : null;
    }

    public function cancelledAt(): ?Carbon
    {
        $cancelled = $this->attributes['cancelled_at'] ?? null;

        if ($cancelled === null) {
            return null;
        }

        return is_string($cancelled) || is_int($cancelled) ? $this->parseDateTime($cancelled) : null;
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
