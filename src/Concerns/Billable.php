<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

trait Billable
{
    use Chargeable;
    use HasPaymentMethods;
    use HasTapCustomer;
}
