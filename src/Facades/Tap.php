<?php

declare(strict_types=1);

namespace TapPay\Tap\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \TapPay\Tap\Services\ChargeService charges()
 * @method static \TapPay\Tap\Services\CustomerService customers()
 * @method static \TapPay\Tap\Services\RefundService refunds()
 * @method static \TapPay\Tap\Services\AuthorizeService authorizations()
 * @method static \TapPay\Tap\Services\TokenService tokens()
 * @method static \TapPay\Tap\Services\CardService cards()
 * @method static \TapPay\Tap\Services\InvoiceService invoices()
 * @method static \TapPay\Tap\Services\SubscriptionService subscriptions()
 * @method static \TapPay\Tap\Http\Client getClient()
 *
 * @see \TapPay\Tap\Tap
 */
class Tap extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tap';
    }
}
