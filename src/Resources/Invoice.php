<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;
use TapPay\Tap\Enums\InvoiceStatus;

class Invoice extends Resource
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

        if (!$expiry) {
            return null;
        }

        try {
            return new DateTime($expiry);
        } catch (\Exception) {
            return null;
        }
    }

    public function paidAt(): ?DateTime
    {
        $paid = $this->attributes['paid_at'] ?? null;

        if (!$paid) {
            return null;
        }

        try {
            return new DateTime($paid);
        } catch (\Exception) {
            return null;
        }
    }

    public function chargeId(): ?string
    {
        return $this->attributes['charge_id'] ?? $this->get('charge.id');
    }

    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
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

    /**
     * Check if invoice ID has valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'inv_');
    }
}