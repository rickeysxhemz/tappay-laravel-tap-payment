<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

trait HasCrudOperations
{
    use HasCreateOperation;
    use HasDeleteOperation;
    use HasListOperation;
    use HasRetrieveOperation;
    use HasUpdateOperation;
}
