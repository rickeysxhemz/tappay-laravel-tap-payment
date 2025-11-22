<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TapPay\Tap\TapServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers
     */
    protected function getPackageProviders($app): array
    {
        return [
            TapServiceProvider::class,
        ];
    }

    /**
     * Get package aliases
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Tap' => \TapPay\Tap\Facades\Tap::class,
        ];
    }

    /**
     * Define environment setup
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('tap.secret_key', 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ');
        $app['config']->set('tap.publishable_key', 'pk_test_EtHFV4BuPQokJT6jiROls87Y');
        $app['config']->set('tap.currency', 'USD');
        $app['config']->set('tap.base_url', 'https://api.tap.company/v2/');
    }
}
