<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\PayoutService;
use TapPay\Tap\Tests\TestCase;

class PayoutServiceTest extends TestCase
{
    protected PayoutService $payoutService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payoutService = new PayoutService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_retrieve_a_payout(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_123456',
            'merchant' => 'merchant_test_789',
            'amount' => 1000.00,
            'currency' => 'SAR',
            'status' => 'PAID',
            'arrival_date' => '2024-01-15',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-07',
            'transaction_count' => 25,
            'fee' => 10.00,
        ])));

        $payout = $this->payoutService->retrieve('payout_test_123456');

        $this->assertSame('payout_test_123456', $payout->id());
        $this->assertSame('merchant_test_789', $payout->merchantId());
        $this->assertSame(1000.00, $payout->amount()->toDecimal());
        $this->assertSame('SAR', $payout->currency());
        $this->assertTrue($payout->isComplete());
        $this->assertFalse($payout->isPending());
        $this->assertFalse($payout->isFailed());
        $this->assertSame('2024-01-15', $payout->arrivalDate());
        $this->assertSame('2024-01-01', $payout->periodStart());
        $this->assertSame('2024-01-07', $payout->periodEnd());
        $this->assertSame(25, $payout->transactionCount());
        $this->assertSame(10.00, $payout->feeAmount()->toDecimal());
    }

    #[Test]
    public function it_can_list_payouts(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'payouts' => [
                [
                    'id' => 'payout_test_1',
                    'merchant' => 'merchant_test_1',
                    'amount' => 500.00,
                    'currency' => 'SAR',
                    'status' => 'PAID',
                ],
                [
                    'id' => 'payout_test_2',
                    'merchant' => 'merchant_test_1',
                    'amount' => 750.00,
                    'currency' => 'SAR',
                    'status' => 'PENDING',
                ],
            ],
        ])));

        $payouts = $this->payoutService->list(['limit' => 10]);

        $this->assertCount(2, $payouts);
        $this->assertSame('payout_test_1', $payouts[0]->id());
        $this->assertTrue($payouts[0]->isComplete());
        $this->assertSame('payout_test_2', $payouts[1]->id());
        $this->assertTrue($payouts[1]->isPending());
    }

    #[Test]
    public function it_can_list_payouts_by_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'payouts' => [
                [
                    'id' => 'payout_test_1',
                    'merchant' => 'merchant_test_123',
                    'amount' => 1000.00,
                    'currency' => 'SAR',
                    'status' => 'PAID',
                ],
            ],
        ])));

        $payouts = $this->payoutService->listByMerchant('merchant_test_123');

        $this->assertCount(1, $payouts);
        $this->assertSame('merchant_test_123', $payouts[0]->merchantId());
    }

    #[Test]
    public function it_can_download_payout_report(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'url' => 'https://api.tap.company/v2/exports/payout_report.csv',
            'status' => 'ready',
        ])));

        $result = $this->payoutService->download([
            'merchant' => 'merchant_test_123',
            'period' => [
                'start' => '2024-01-01',
                'end' => '2024-01-31',
            ],
        ]);

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertSame('ready', $result['status']);
    }

    #[Test]
    public function it_handles_empty_payout_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'payouts' => [],
        ])));

        $payouts = $this->payoutService->list([]);

        $this->assertCount(0, $payouts);
    }

    #[Test]
    public function it_handles_pending_payout(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_pending',
            'merchant' => 'merchant_test_789',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'PENDING',
        ])));

        $payout = $this->payoutService->retrieve('payout_test_pending');

        $this->assertTrue($payout->isPending());
        $this->assertFalse($payout->isComplete());
        $this->assertFalse($payout->isFailed());
    }

    #[Test]
    public function it_handles_in_progress_payout(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_progress',
            'merchant' => 'merchant_test_789',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'IN_PROGRESS',
        ])));

        $payout = $this->payoutService->retrieve('payout_test_progress');

        $this->assertTrue($payout->isPending());
        $this->assertFalse($payout->isComplete());
    }

    #[Test]
    public function it_handles_failed_payout(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_failed',
            'merchant' => 'merchant_test_789',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ])));

        $payout = $this->payoutService->retrieve('payout_test_failed');

        $this->assertTrue($payout->isFailed());
        $this->assertFalse($payout->isPending());
        $this->assertFalse($payout->isComplete());
    }

    #[Test]
    public function it_handles_payout_with_bank_account(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_bank',
            'merchant' => 'merchant_test_789',
            'amount' => 1000.00,
            'currency' => 'SAR',
            'status' => 'PAID',
            'bank_account' => [
                'iban' => 'SA0380000000608010167519',
                'bank_name' => 'Al Rajhi Bank',
            ],
        ])));

        $payout = $this->payoutService->retrieve('payout_test_bank');

        $this->assertIsArray($payout->bankAccount());
        $this->assertSame('Al Rajhi Bank', $payout->bankAccount()['bank_name']);
    }

    #[Test]
    public function it_calculates_net_amount(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'payout_test_net',
            'merchant' => 'merchant_test_789',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'PAID',
            'fee' => 2.50,
        ])));

        $payout = $this->payoutService->retrieve('payout_test_net');

        $this->assertSame(100.00, $payout->amount()->toDecimal());
        $this->assertSame(2.50, $payout->feeAmount()->toDecimal());
        $this->assertSame(97.50, $payout->netAmount()->toDecimal());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->payoutService->retrieve('payout_test_123');
    }

    #[Test]
    public function it_throws_api_error_exception_on_404(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Payout not found',
        ])));

        try {
            $this->payoutService->retrieve('payout_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Payout not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
