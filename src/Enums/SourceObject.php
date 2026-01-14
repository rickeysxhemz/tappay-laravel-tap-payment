<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum SourceObject: string
{
    case SRC_CARD = 'src_card';
    case SRC_ALL = 'src_all';

    case SRC_KNET = 'src_kw.knet';
    case SRC_KFAST = 'src_kw.kfast';
    case SRC_MADA = 'src_sa.mada';
    case SRC_BENEFIT = 'src_bh.benefit';
    case SRC_OMANNET = 'src_omannet';
    case SRC_QPAY = 'src_qa.qpay';
    case SRC_FAWRY = 'src_eg.fawry';

    case SRC_STC_PAY = 'src_sa.stcpay';

    case SRC_TABBY = 'src_tabby.installement';
    case SRC_DEEMA = 'src_deema';

    public static function isToken(string $sourceId): bool
    {
        return str_starts_with($sourceId, 'tok_');
    }

    public static function isAuthorization(string $sourceId): bool
    {
        return str_starts_with($sourceId, 'auth_');
    }

    public static function sourceRequiresRedirect(string $sourceId): bool
    {
        return ! self::isToken($sourceId) && ! self::isAuthorization($sourceId);
    }

    public function isRegionalMethod(): bool
    {
        return match ($this) {
            self::SRC_KNET,
            self::SRC_KFAST,
            self::SRC_MADA,
            self::SRC_BENEFIT,
            self::SRC_OMANNET,
            self::SRC_QPAY,
            self::SRC_FAWRY => true,
            default => false,
        };
    }

    public function isDigitalWallet(): bool
    {
        return $this === self::SRC_STC_PAY;
    }

    public function isBNPL(): bool
    {
        return match ($this) {
            self::SRC_TABBY, self::SRC_DEEMA => true,
            default => false,
        };
    }

    public function getCountry(): ?string
    {
        return match ($this) {
            self::SRC_KNET, self::SRC_KFAST => 'KW',
            self::SRC_MADA, self::SRC_STC_PAY => 'SA',
            self::SRC_BENEFIT => 'BH',
            self::SRC_OMANNET => 'OM',
            self::SRC_QPAY => 'QA',
            self::SRC_FAWRY => 'EG',
            default => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SRC_CARD => 'Card Payment',
            self::SRC_ALL => 'All Payment Methods',
            self::SRC_KNET => 'KNET',
            self::SRC_KFAST => 'KFAST',
            self::SRC_MADA => 'Mada',
            self::SRC_BENEFIT => 'Benefit',
            self::SRC_OMANNET => 'OmanNet',
            self::SRC_QPAY => 'QPay',
            self::SRC_FAWRY => 'Fawry',
            self::SRC_STC_PAY => 'STC Pay',
            self::SRC_TABBY => 'Tabby',
            self::SRC_DEEMA => 'Deema',
        };
    }
}
