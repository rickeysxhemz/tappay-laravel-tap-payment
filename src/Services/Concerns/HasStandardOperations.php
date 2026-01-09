<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

trait HasStandardOperations
{
    use HasCreateOperation;
    use HasListOperation;
    use HasRetrieveOperation;
    use HasUpdateOperation;
}
