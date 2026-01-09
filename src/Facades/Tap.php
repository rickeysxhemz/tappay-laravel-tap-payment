<?php

declare(strict_types=1);

namespace TapPay\Tap\Facades;

use Illuminate\Support\Facades\Facade;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Services\CardService;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Services\DestinationService;
use TapPay\Tap\Services\InvoiceService;
use TapPay\Tap\Services\MerchantService;
use TapPay\Tap\Services\PayoutService;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Services\SubscriptionService;
use TapPay\Tap\Services\TokenService;
use TapPay\Tap\Tap as TapService;

/**
 * @method static ChargeService charges()
 * @method static CustomerService customers()
 * @method static RefundService refunds()
 * @method static AuthorizeService authorizations()
 * @method static TokenService tokens()
 * @method static CardService cards()
 * @method static InvoiceService invoices()
 * @method static SubscriptionService subscriptions()
 * @method static MerchantService merchants()
 * @method static DestinationService destinations()
 * @method static PayoutService payouts()
 * @method static bool registersRoutes()
 *
 * @see TapService
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
