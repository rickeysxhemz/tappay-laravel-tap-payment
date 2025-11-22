<?php

use Illuminate\Support\Facades\Route;
use TapPay\Tap\Webhooks\WebhookController;

Route::post('tap/webhook', WebhookController::class)
    ->name('tap.webhook');
