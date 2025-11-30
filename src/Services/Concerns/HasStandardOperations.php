<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

/**
 * Aggregate trait for standard CRUD without delete (create + retrieve + update + list)
 * Used by: ChargeService, RefundService, AuthorizeService, SubscriptionService
 */
trait HasStandardOperations
{
    use HasCreateOperation;
    use HasRetrieveOperation;
    use HasUpdateOperation;
    use HasListOperation;
}