<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

/**
 * Payment agreement types for recurring/saved card transactions
 *
 * Used with payment_agreement parameter to specify the nature of the transaction.
 */
enum AgreementType: string
{
    /**
     * Unscheduled payments - variable amount, variable timing
     * Example: Top-ups, pay-as-you-go services
     */
    case UNSCHEDULED = 'UNSCHEDULED';

    /**
     * Recurring payments - fixed amount, fixed timing
     * Example: Monthly subscriptions, membership fees
     */
    case RECURRING = 'RECURRING';

    /**
     * Installment payments - fixed amount split into parts
     * Example: Buy-now-pay-later, payment plans
     */
    case INSTALLMENT = 'INSTALLMENT';
}
