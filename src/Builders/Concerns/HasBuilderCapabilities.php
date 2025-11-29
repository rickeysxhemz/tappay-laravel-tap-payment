<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Aggregate trait for core builder capabilities
 */
trait HasBuilderCapabilities
{
    use HasAmount;
    use HasCustomer;
    use HasMetadata;
    use HasRedirects;
    use HasReferences;
}
