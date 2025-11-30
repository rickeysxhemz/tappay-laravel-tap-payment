<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

/**
 * Aggregate trait for full CRUD operations (create + retrieve + update + list + delete)
 * Used by: CustomerService, InvoiceService, MerchantService
 */
trait HasCrudOperations
{
    use HasCreateOperation;
    use HasRetrieveOperation;
    use HasUpdateOperation;
    use HasListOperation;
    use HasDeleteOperation;
}