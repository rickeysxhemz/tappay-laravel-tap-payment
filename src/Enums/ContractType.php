<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

/**
 * Contract types for payment agreement contracts
 *
 * Used with payment_agreement.contract parameter.
 */
enum ContractType: string
{
    /**
     * Unscheduled contract - variable amount, variable timing
     */
    case UNSCHEDULED = 'UNSCHEDULED';

    /**
     * Recurring contract - fixed amount, fixed timing
     */
    case RECURRING = 'RECURRING';

    /**
     * Installment contract - fixed amount split into parts
     */
    case INSTALLMENT = 'INSTALLMENT';
}