<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

trait Billable
{
    use HasTapCustomer;
    use Chargeable;
    use HasPaymentMethods;
}