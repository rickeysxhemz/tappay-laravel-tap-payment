<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Services\ChargeService;

class ChargeBuilder extends AbstractBuilder
{
    protected ChargeService $service;

    public function __construct(ChargeService $service)
    {
        $this->service = $service;

        // Set default currency from config
        $this->data['currency'] = config('tap.currency', 'USD');
    }

    /**
     * Set the payment source
     */
    public function source(string|SourceObject $source): self
    {
        $sourceValue = $source instanceof SourceObject ? $source->value : $source;
        $this->data['source'] = ['id' => $sourceValue];
        return $this;
    }

    /**
     * Use card payment (redirect to hosted page)
     */
    public function withCard(): self
    {
        return $this->source(SourceObject::SRC_CARD);
    }

    /**
     * Use all available payment methods
     */
    public function withAllMethods(): self
    {
        return $this->source(SourceObject::SRC_ALL);
    }

    /**
     * Use KNET (Kuwait)
     */
    public function withKNET(): self
    {
        return $this->source(SourceObject::SRC_KNET);
    }

    /**
     * Use MADA (Saudi Arabia)
     */
    public function withMADA(): self
    {
        return $this->source(SourceObject::SRC_MADA);
    }

    /**
     * Use Benefit (Bahrain)
     */
    public function withBenefit(): self
    {
        return $this->source(SourceObject::SRC_BENEFIT);
    }

    /**
     * Use OmanNet (Oman)
     */
    public function withOmanNet(): self
    {
        return $this->source(SourceObject::SRC_OMANNET);
    }

    /**
     * Use NAPS (Qatar)
     */
    public function withNAPS(): self
    {
        return $this->source(SourceObject::SRC_NAPS);
    }

    /**
     * Use a token (for saved cards or Apple Pay/Google Pay)
     */
    public function withToken(string $tokenId): self
    {
        $this->data['source'] = ['id' => $tokenId];
        return $this;
    }

    /**
     * Capture a previous authorization
     */
    public function captureAuthorization(string $authId): self
    {
        $this->data['source'] = ['id' => $authId];
        return $this;
    }

    /**
     * Save the card for future use
     */
    public function saveCard(bool $save = true): self
    {
        $this->data['save_card'] = $save;
        return $this;
    }

    /**
     * Set the statement descriptor
     */
    public function statementDescriptor(string $descriptor): self
    {
        $this->data['statement_descriptor'] = $descriptor;
        return $this;
    }

    /**
     * Set receipt settings
     */
    public function receipt(array $receipt): self
    {
        $this->data['receipt'] = $receipt;
        return $this;
    }

    /**
     * Enable email receipt
     */
    public function emailReceipt(bool $email = true): self
    {
        $this->data['receipt'] = ['email' => $email];
        return $this;
    }

    /**
     * Enable SMS receipt
     */
    public function smsReceipt(bool $sms = true): self
    {
        if (!isset($this->data['receipt'])) {
            $this->data['receipt'] = [];
        }
        $this->data['receipt']['sms'] = $sms;
        return $this;
    }

    /**
     * Set auto capture (for authorizations)
     */
    public function auto(array $auto): self
    {
        $this->data['auto'] = $auto;
        return $this;
    }

    /**
     * Build and create the charge
     */
    public function create(): Charge
    {
        return $this->service->create($this->toArray());
    }
}
