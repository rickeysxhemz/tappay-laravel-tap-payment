<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\InvoiceStatus;
use TapPay\Tap\Services\InvoiceService;
use TapPay\Tap\Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceService = new InvoiceService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'inv_test_123456',
            'status' => 'DRAFT',
            'amount' => 100.00,
            'currency' => 'SAR',
        ])));

        $invoice = $this->invoiceService->create([
            'amount' => 100.00,
            'currency' => 'SAR',
            'customer' => ['id' => 'cus_test_123'],
        ]);

        $this->assertSame('inv_test_123456', $invoice->id());
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->status());
    }

    #[Test]
    public function it_can_finalize_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'inv_test_123456',
            'status' => 'PENDING',
            'amount' => 100.00,
            'currency' => 'SAR',
        ])));

        $invoice = $this->invoiceService->finalize('inv_test_123456');

        $this->assertSame('inv_test_123456', $invoice->id());
        $this->assertSame(InvoiceStatus::PENDING, $invoice->status());
    }

    #[Test]
    public function it_can_send_invoice_reminder(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'inv_test_123456',
            'status' => 'PENDING',
            'amount' => 100.00,
            'currency' => 'SAR',
        ])));

        $invoice = $this->invoiceService->remind('inv_test_123456');

        $this->assertSame('inv_test_123456', $invoice->id());
        $this->assertSame(InvoiceStatus::PENDING, $invoice->status());
    }

    #[Test]
    public function it_can_cancel_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([])));

        $this->invoiceService->cancel('inv_test_123456');

        $this->assertTrue(true); // No exception means success
    }

    #[Test]
    public function it_can_retrieve_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'inv_test_123456',
            'status' => 'PAID',
            'amount' => 100.00,
            'currency' => 'SAR',
        ])));

        $invoice = $this->invoiceService->retrieve('inv_test_123456');

        $this->assertSame('inv_test_123456', $invoice->id());
        $this->assertSame(InvoiceStatus::PAID, $invoice->status());
        $this->assertTrue($invoice->isSuccessful());
    }

    #[Test]
    public function it_can_list_invoices(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'invoices' => [
                [
                    'id' => 'inv_test_1',
                    'status' => 'DRAFT',
                    'amount' => 100.00,
                    'currency' => 'SAR',
                ],
                [
                    'id' => 'inv_test_2',
                    'status' => 'PENDING',
                    'amount' => 200.00,
                    'currency' => 'SAR',
                ],
            ],
        ])));

        $invoices = $this->invoiceService->list(['limit' => 10]);

        $this->assertCount(2, $invoices);
        $this->assertSame('inv_test_1', $invoices[0]->id());
        $this->assertSame('inv_test_2', $invoices[1]->id());
    }

    #[Test]
    public function it_can_update_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'inv_test_123456',
            'status' => 'DRAFT',
            'amount' => 150.00,
            'currency' => 'SAR',
        ])));

        $invoice = $this->invoiceService->update('inv_test_123456', [
            'amount' => 150.00,
        ]);

        $this->assertSame('inv_test_123456', $invoice->id());
    }

    #[Test]
    public function it_can_delete_an_invoice(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([])));

        $this->invoiceService->delete('inv_test_123456');

        $this->assertTrue(true); // No exception means success
    }

    #[Test]
    public function it_throws_exception_for_invalid_finalize_id(): void
    {
        $this->expectException(\TapPay\Tap\Exceptions\InvalidRequestException::class);
        $this->expectExceptionMessage('Invoice ID must start with "inv_"');

        $this->invoiceService->finalize('invalid_id');
    }

    #[Test]
    public function it_throws_exception_for_invalid_remind_id(): void
    {
        $this->expectException(\TapPay\Tap\Exceptions\InvalidRequestException::class);
        $this->expectExceptionMessage('Invoice ID must start with "inv_"');

        $this->invoiceService->remind('bad_invoice_id');
    }

    #[Test]
    public function it_throws_exception_for_invalid_cancel_id(): void
    {
        $this->expectException(\TapPay\Tap\Exceptions\InvalidRequestException::class);
        $this->expectExceptionMessage('Invoice ID must start with "inv_"');

        $this->invoiceService->cancel('chg_wrong_prefix');
    }
}
