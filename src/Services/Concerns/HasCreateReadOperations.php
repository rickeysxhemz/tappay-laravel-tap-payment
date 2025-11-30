<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

/**
 * Aggregate trait for create and read operations (create + retrieve + list)
 * Used by: TokenService
 */
trait HasCreateReadOperations
{
    use HasCreateOperation;
    use HasListOperation;
    use HasRetrieveOperation;
}
