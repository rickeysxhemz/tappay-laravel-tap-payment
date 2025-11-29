<?php

declare(strict_types=1);

use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Enums\SubscriptionStatus;
use TapPay\Tap\Resources\Subscription;

test('can create subscription resource from array', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription)->toBeInstanceOf(Subscription::class);
})->group('unit');

test('can get subscription ID', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->id())->toBe('sub_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get subscription amount', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->amount())->toBe(99.99);
})->group('unit');

test('can get subscription currency', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->currency())->toBe('SAR');
})->group('unit');

test('can get subscription status', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->status())->toBeInstanceOf(SubscriptionStatus::class)
        ->and($subscription->status()->value)->toBe('ACTIVE');
})->group('unit');

test('can get customer ID', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->customerId())->toBe('cus_TS02A2220231616Xm0B1234567');
})->group('unit');

test('can get subscription interval', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->interval())->toBeInstanceOf(SubscriptionInterval::class)
        ->and($subscription->interval()->value)->toBe('MONTHLY');
})->group('unit');

test('can get subscription period', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->period())->toBe(1);
})->group('unit');

test('can get trial days', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->trialDays())->toBe(14);
})->group('unit');

test('can get start date', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->startDate())->toBeInstanceOf(DateTime::class);
})->group('unit');

test('can get current period start', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->currentPeriodStart())->toBeInstanceOf(DateTime::class);
})->group('unit');

test('can get current period end', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->currentPeriodEnd())->toBeInstanceOf(DateTime::class);
})->group('unit');

test('can get subscription metadata', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->metadata())->toBeArray()
        ->and($subscription->metadata()['plan'])->toBe('premium');
})->group('unit');

// Status helper tests
test('isActive returns true for ACTIVE status', function () {
    $subscription = new Subscription(['status' => 'ACTIVE']);

    expect($subscription->isActive())->toBeTrue();
})->group('unit');

test('isPaused returns true for PAUSED status', function () {
    $subscription = new Subscription(['status' => 'PAUSED']);

    expect($subscription->isPaused())->toBeTrue();
})->group('unit');

test('isCancelled returns true for CANCELLED status', function () {
    $subscription = new Subscription(['status' => 'CANCELLED']);

    expect($subscription->isCancelled())->toBeTrue();
})->group('unit');

test('isTrialing returns true for TRIALING status', function () {
    $subscription = new Subscription(['status' => 'TRIALING']);

    expect($subscription->isTrialing())->toBeTrue();
})->group('unit');

test('onTrial is alias for isTrialing', function () {
    $subscription = new Subscription(['status' => 'TRIALING']);

    expect($subscription->onTrial())->toBe($subscription->isTrialing());
})->group('unit');

test('requiresAttention returns true for PAST_DUE status', function () {
    $subscription = new Subscription(['status' => 'PAST_DUE']);

    expect($subscription->requiresAttention())->toBeTrue();
})->group('unit');

// hasValidId tests
test('hasValidId returns true for valid subscription ID', function () {
    $subscription = new Subscription(['id' => 'sub_12345']);

    expect($subscription->hasValidId())->toBeTrue();
})->group('unit');

test('hasValidId returns false for empty ID', function () {
    $subscription = new Subscription([]);

    expect($subscription->hasValidId())->toBeFalse();
})->group('unit');

test('hasValidId returns false for ID without sub prefix', function () {
    $subscription = new Subscription(['id' => 'inv_12345']);

    expect($subscription->hasValidId())->toBeFalse();
})->group('unit');

// Date parsing tests
test('cancelledAt returns DateTime when cancelled', function () {
    $subscription = new Subscription(['cancelled_at' => '2025-06-15T10:30:00Z']);

    expect($subscription->cancelledAt())->toBeInstanceOf(DateTime::class);
})->group('unit');

test('cancelledAt returns null when not cancelled', function () {
    $subscription = new Subscription(['cancelled_at' => null]);

    expect($subscription->cancelledAt())->toBeNull();
})->group('unit');

test('startDate handles timestamp format', function () {
    $subscription = new Subscription(['start_date' => 1616439916]);

    expect($subscription->startDate())->toBeInstanceOf(DateTime::class);
})->group('unit');

// Default values tests
test('returns empty string for missing id', function () {
    $subscription = new Subscription([]);

    expect($subscription->id())->toBe('');
})->group('unit');

test('returns zero for missing amount', function () {
    $subscription = new Subscription([]);

    expect($subscription->amount())->toBe(0.0);
})->group('unit');

test('returns empty string for missing currency', function () {
    $subscription = new Subscription([]);

    expect($subscription->currency())->toBe('');
})->group('unit');

test('returns CANCELLED for missing status', function () {
    $subscription = new Subscription([]);

    expect($subscription->status())->toBe(SubscriptionStatus::CANCELLED);
})->group('unit');

test('returns null for missing customerId', function () {
    $subscription = new Subscription([]);

    expect($subscription->customerId())->toBeNull();
})->group('unit');

test('returns null for missing interval', function () {
    $subscription = new Subscription([]);

    expect($subscription->interval())->toBeNull();
})->group('unit');

test('returns 1 for missing period', function () {
    $subscription = new Subscription([]);

    expect($subscription->period())->toBe(1);
})->group('unit');

test('returns 0 for missing trialDays', function () {
    $subscription = new Subscription([]);

    expect($subscription->trialDays())->toBe(0);
})->group('unit');

test('returns null for missing startDate', function () {
    $subscription = new Subscription([]);

    expect($subscription->startDate())->toBeNull();
})->group('unit');

test('returns empty array for missing metadata', function () {
    $subscription = new Subscription([]);

    expect($subscription->metadata())->toBe([]);
})->group('unit');

// Inherited methods tests
test('can convert to array', function () {
    $data = loadFixture('subscription.json');
    $subscription = new Subscription($data);

    expect($subscription->toArray())->toBe($data);
})->group('unit');

test('isEmpty returns false with data', function () {
    $subscription = new Subscription(['id' => 'sub_123']);

    expect($subscription->isEmpty())->toBeFalse();
})->group('unit');

test('isEmpty returns true with no data', function () {
    $subscription = new Subscription([]);

    expect($subscription->isEmpty())->toBeTrue();
})->group('unit');
