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
     *
     * @return bool
     */
    public function requiresRedirect(): bool
    {
        return match($this) {
            self::TOKEN, self::AUTH => false,
            default => true,
        };
    }

    /**
     * Check if this is a regional payment method
     *
     * @return bool
     */
    public function isRegionalMethod(): bool
    {
        return match($this) {
            self::SRC_KNET,
            self::SRC_KFAST,
            self::SRC_MADA,
            self::SRC_BENEFIT,
            self::SRC_OMANNET,
            self::SRC_NAPS,
            self::SRC_FAWRY => true,
            default => false,
        };
    }

    /**
     * Check if this is a digital wallet
     *
     * @return bool
     */
    public function isDigitalWallet(): bool
    {
        return match($this) {
            self::SRC_STC_PAY => true,
            default => false,
        };
    }

    /**
     * Check if this is a BNPL method
     *
     * @return bool
     */
    public function isBNPL(): bool
    {
        return match($this) {
            self::SRC_TABBY, self::SRC_DEEMA => true,
            default => false,
        };
    }

    /**
     * Check if this is a token source
     *
     * @return bool
     */
    public function isToken(): bool
    {
        return match($this) {
            self::TOKEN => true,
            default => false,
        };
    }

    /**
     * Check if this is an authorization source
     *
     * @return bool
     */
    public function isAuthorization(): bool
    {
        return match($this) {
            self::AUTH => true,
            default => false,
        };
    }

    /**
     * Get the country code for regional payment methods
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return match($this) {
            self::SRC_KNET, self::SRC_KFAST => 'KW',
            self::SRC_MADA => 'SA',
            self::SRC_BENEFIT => 'BH',
            self::SRC_OMANNET => 'OM',
            self::SRC_NAPS => 'QA',
            self::SRC_FAWRY => 'EG',
            self::SRC_STC_PAY => 'SA',
            default => null,
        };
    }

    /**
     * Get human-readable label
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::SRC_CARD => 'Card Payment',
            self::SRC_ALL => 'All Payment Methods',
            self::SRC_KNET => 'KNET',
            self::SRC_KFAST => 'KFAST',
            self::SRC_MADA => 'Mada',
            self::SRC_BENEFIT => 'Benefit',
            self::SRC_OMANNET => 'OmanNet',
            self::SRC_NAPS => 'NAPS',
            self::SRC_FAWRY => 'Fawry',
            self::SRC_STC_PAY => 'STC Pay',
            self::SRC_TABBY => 'Tabby',
            self::SRC_DEEMA => 'Deema',
            self::TOKEN => 'Token',
            self::AUTH => 'Authorization',
        };
    }
}
