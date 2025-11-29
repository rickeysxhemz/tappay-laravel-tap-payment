<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

/**
 * Aggregate trait for payment resources (Charge, Authorize)
 * Combines: HasMoney, HasPaymentStatus, HasCustomer, HasTransaction
 */
trait HasPaymentDetails
{
    use HasCustomer;
    use HasMoney;
    use HasPaymentStatus;
    use HasTransaction;
}
