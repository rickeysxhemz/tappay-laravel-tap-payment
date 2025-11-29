<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;
use TapPay\Tap\Enums\InvoiceStatus;

class Invoice extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'inv_';
    }

    public function amount(): float
    {
        return (float) ($this->attributes['amount'] ?? 0);
    }

    public function currency(): string
    {
        return $this->attributes['currency'] ?? '';
    }

    public function status(): InvoiceStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'FAILED');

        return InvoiceStatus::tryFrom($status) ?? InvoiceStatus::FAILED;
    }

    public function description(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    public function customerId(): ?string
    {
        return $this->get('customer.id') ?? $this->attributes['customer_id'] ?? null;
    }

    public function url(): ?string
    {
        return $this->attributes['url'] ?? $this->attributes['invoice_url'] ?? null;
    }

    public function expiresAt(): ?DateTime
    {
        $expiry = $this->attributes['expiry'] ?? $this->attributes['expires_at'] ?? null;

        return $expiry ? $this->parseDateTime($expiry) : null;
    }

    public function paidAt(): ?DateTime
    {
        $paid = $this->attributes['paid_at'] ?? null;

        return $paid ? $this->parseDateTime($paid) : null;
    }

    public function chargeId(): ?string
    {
        return $this->attributes['charge_id'] ?? $this->get('charge.id');
    }

    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }

    public function isExpired(): bool
    {
        return $this->status() === InvoiceStatus::EXPIRED;
    }
}
