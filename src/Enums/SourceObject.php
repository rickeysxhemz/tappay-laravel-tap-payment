<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum SourceObject: string
{
    // Card payments
    case SRC_CARD = 'src_card';
    case SRC_ALL = 'src_all';

    // Regional payment methods - Kuwait
    case SRC_KNET = 'src_kw.knet';
    case SRC_KFAST = 'src_kw.kfast';

    // Regional payment methods - Saudi Arabia
    case SRC_MADA = 'src_sa.mada';

    // Regional payment methods - Bahrain
    case SRC_BENEFIT = 'src_bh.benefit';

    // Regional payment methods - Oman
    case SRC_OMANNET = 'src_om.omannet';

    // Regional payment methods - Qatar
    case SRC_NAPS = 'src_qa.naps';

    // Regional payment methods - Egypt
    case SRC_FAWRY = 'src_eg.fawry';

    // Digital wallets
    case SRC_STC_PAY = 'src_stcpay';

    // Buy Now Pay Later
    case SRC_TABBY = 'src_tabby';
    case SRC_DEEMA = 'src_deema';

    // Token-based (for saved cards, Apple Pay, Google Pay, Samsung Pay)
    case TOKEN = 'tok_';

    // Authorization-based (for capture flow)
    case AUTH = 'auth_';

    /**
     * Check if this source requires redirect flow
     */
    public function requiresRedirect(): bool
    {
        return !in_array($this, [self::TOKEN, self::AUTH]);
    }

    /**
     * Check if this is a regional payment method
     */
    public function isRegionalMethod(): bool
    {
        return in_array($this, [
            self::SRC_KNET,
            self::SRC_KFAST,
            self::SRC_MADA,
            self::SRC_BENEFIT,
            self::SRC_OMANNET,
            self::SRC_NAPS,
            self::SRC_FAWRY,
        ]);
    }

    /**
     * Check if this is a digital wallet
     */
    public function isDigitalWallet(): bool
    {
        return $this === self::SRC_STC_PAY;
    }

    /**
     * Check if this is a BNPL method
     */
    public function isBNPL(): bool
    {
        return $this === self::SRC_TABBY || $this === self::SRC_DEEMA;
    }

    /**
     * Check if this is a token source
     */
    public function isToken(): bool
    {
        return $this === self::TOKEN;
    }

    /**
     * Check if this is an authorization source
     */
    public function isAuthorization(): bool
    {
        return $this === self::AUTH;
    }
}
