<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Aggregate trait for charge-specific builder capabilities
 */
trait HasChargeCapabilities
{
    use HasAuthentication;
    use HasDestinations;
    use HasPaymentAgreement;
    use HasReceipt;
    use HasSource;
}
