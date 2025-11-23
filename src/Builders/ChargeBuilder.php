<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use InvalidArgumentException;
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
     *
     * @param string|SourceObject $source Source ID or SourceObject enum
     * @return self
     */
    public function source(string|SourceObject $source): self
    {
        $sourceValue = $source instanceof SourceObject ? $source->value : $source;
        $this->data['source'] = ['id' => $sourceValue];
        return $this;
    }

    /**
     * Use card payment (redirect to hosted page)
     *
     * @return self
     */
    public function withCard(): self
    {
        return $this->source(SourceObject::SRC_CARD);
    }

    /**
     * Use all available payment methods
     *
     * @return self
     */
    public function withAllMethods(): self
    {
        return $this->source(SourceObject::SRC_ALL);
    }

    /**
     * Use KNET (Kuwait)
     *
     * @return self
     */
    public function withKNET(): self
    {
        return $this->source(SourceObject::SRC_KNET);
    }

    /**
     * Use MADA (Saudi Arabia)
     *
     * @return self
     */
    public function withMADA(): self
    {
        return $this->source(SourceObject::SRC_MADA);
    }

    /**
     * Use Benefit (Bahrain)
     *
     * @return self
     */
    public function withBenefit(): self
    {
        return $this->source(SourceObject::SRC_BENEFIT);
    }

    /**
     * Use OmanNet (Oman)
     *
     * @return self
     */
    public function withOmanNet(): self
    {
        return $this->source(SourceObject::SRC_OMANNET);
    }

    /**
     * Use NAPS (Qatar)
     *
     * @return self
     */
    public function withNAPS(): self
    {
        return $this->source(SourceObject::SRC_NAPS);
    }

    /**
     * Use a token (for saved cards or Apple Pay/Google Pay)
     *
     * @param string $tokenId Token ID (must start with 'tok_')
     * @return self
     * @throws InvalidArgumentException
     */
    public function withToken(string $tokenId): self
    {
        if (!str_starts_with($tokenId, 'tok_')) {
            throw new InvalidArgumentException('Token ID must start with "tok_"');
        }
        return $this->source($tokenId);
    }

    /**
     * Capture a previous authorization
     *
     * @param string $authId Authorization ID (must start with 'auth_')
     * @return self
     * @throws InvalidArgumentException
     */
    public function captureAuthorization(string $authId): self
    {
        if (!str_starts_with($authId, 'auth_')) {
            throw new InvalidArgumentException('Authorization ID must start with "auth_"');
        }
        return $this->source($authId);
    }

    /**
     * Save the card for future use
     *
     * @param bool $save Whether to save the card
     * @return self
     */
    public function saveCard(bool $save = true): self
    {
        $this->data['save_card'] = $save;
        return $this;
    }

    /**
     * Set the statement descriptor
     *
     * @param string $descriptor Text shown on customer's statement
     * @return self
     */
    public function statementDescriptor(string $descriptor): self
    {
        $this->data['statement_descriptor'] = $descriptor;
        return $this;
    }

    /**
     * Set receipt settings
     *
     * @param array $receipt Receipt configuration array
     * @return self
     */
    public function receipt(array $receipt): self
    {
        $this->data['receipt'] = $receipt;
        return $this;
    }

    /**
     * Enable email receipt
     *
     * @param bool $email Whether to send email receipt
     * @return self
     */
    public function emailReceipt(bool $email = true): self
    {
        $this->data['receipt'] ??= [];
        $this->data['receipt']['email'] = $email;
        return $this;
    }

    /**
     * Enable SMS receipt
     *
     * @param bool $sms Whether to send SMS receipt
     * @return self
     */
    public function smsReceipt(bool $sms = true): self
    {
        $this->data['receipt'] ??= [];
        $this->data['receipt']['sms'] = $sms;
        return $this;
    }

    /**
     * Set auto capture (for authorizations)
     *
     * @param array $auto Auto capture configuration
     * @return self
     */
    public function auto(array $auto): self
    {
        $this->data['auto'] = $auto;
        return $this;
    }

    /**
     * Build and create the charge
     *
     * @return Charge The created charge resource
     */
    public function create(): Charge
    {
        return $this->service->create($this->toArray());
    }
}
