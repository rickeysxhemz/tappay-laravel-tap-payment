<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

trait HasCreateReadOperations
{
    use HasCreateOperation;
    use HasListOperation;
    use HasRetrieveOperation;
}
