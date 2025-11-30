<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

/**
 * Aggregate trait for read-only services (retrieve + list)
 * Used by: DestinationService, PayoutService
 */
trait HasReadOperations
{
    use HasRetrieveOperation;
    use HasListOperation;
}