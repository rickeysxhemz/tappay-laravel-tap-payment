<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Builders\AuthorizationBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Tests\TestCase;

class AuthorizationBuilderTest extends TestCase
{
    protected AuthorizeService $authorizeService;

    protected MoneyContract $money;

    protected function setUp(): void
    {
        parent::setUp();

        $this->money = app(MoneyContract::class);
        $this->authorizeService = new AuthorizeService($this->mockHttpClient(), $this->money);
    }

    protected function createBuilder(): AuthorizationBuilder
    {
        return new AuthorizationBuilder($this->authorizeService, $this->money);
    }

    #[Test]
    public function it_builds_authorization_with_fluent_interface(): void
    {
        $builder = $this->createBuilder();

        $data = $builder
            ->amount(10050)
            ->currency('KWD')
            ->description('Test authorization')
            ->customerId('cus_test_123')
            ->withCard()
            ->saveCard()
            ->redirectUrl('https://example.com/success')
            ->postUrl('https://example.com/webhook')
            ->metadata(['order_id' => '12345'])
            ->toArray();

        $this->assertSame(10.05, $data['amount']);
        $this->assertSame('KWD', $data['currency']);
        $this->assertSame('Test authorization', $data['description']);
        $this->assertSame('cus_test_123', $data['customer']['id']);
        $this->assertSame('src_card', $data['source']['id']);
        $this->assertTrue($data['save_card']);
        $this->assertSame('https://example.com/success', $data['redirect']['url']);
        $this->assertSame('https://example.com/webhook', $data['post']['url']);
        $this->assertSame(['order_id' => '12345'], $data['metadata']);
    }

    #[Test]
    public function it_can_set_auto_capture(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->autoCapture(24)
            ->toArray();

        $this->assertSame('AUTO', $data['auto']['type']);
        $this->assertSame(24, $data['auto']['time']);
    }

    #[Test]
    public function it_can_set_auto_void(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->autoVoid(48)
            ->toArray();

        $this->assertSame('VOID', $data['auto']['type']);
        $this->assertSame(48, $data['auto']['time']);
    }

    #[Test]
    public function it_throws_exception_for_invalid_auto_capture_hours(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auto-capture hours must be between 1 and 168');

        $builder = $this->createBuilder();
        $builder->autoCapture(169);
    }

    #[Test]
    public function it_throws_exception_for_zero_auto_capture_hours(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auto-capture hours must be between 1 and 168');

        $builder = $this->createBuilder();
        $builder->autoCapture(0);
    }

    #[Test]
    public function it_throws_exception_for_invalid_auto_void_hours(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auto-void hours must be between 1 and 168');

        $builder = $this->createBuilder();
        $builder->autoVoid(200);
    }

    #[Test]
    public function it_can_set_idempotent_key_in_reference(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->idempotent('unique-key-123')
            ->toArray();

        $this->assertSame('unique-key-123', $data['reference']['idempotent']);
    }

    #[Test]
    public function it_can_set_three_d_secure(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->threeDSecure(true)
            ->toArray();

        $this->assertTrue($data['threeDSecure']);
    }

    #[Test]
    public function it_can_disable_three_d_secure(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->threeDSecure(false)
            ->toArray();

        $this->assertFalse($data['threeDSecure']);
    }

    #[Test]
    public function it_creates_authorization_via_builder(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_builder',
            'amount' => 25.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $authorization = $this->createBuilder()
            ->amount(2500)
            ->withCard()
            ->description('Builder test')
            ->create();

        $this->assertSame('auth_test_builder', $authorization->id());
        $this->assertSame(25.00, $authorization->amount()->toDecimal());
    }

    #[Test]
    public function it_creates_authorization_with_idempotent_key(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_idempotent',
            'amount' => 25.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $authorization = $this->createBuilder()
            ->amount(2500)
            ->withCard()
            ->idempotent('my-unique-key')
            ->create();

        $this->assertSame('auth_test_idempotent', $authorization->id());
    }

    #[Test]
    public function it_can_use_different_payment_methods(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withKNET()->toArray();
        $this->assertSame('src_kw.knet', $data['source']['id']);

        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withMADA()->toArray();
        $this->assertSame('src_sa.mada', $data['source']['id']);

        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withToken('tok_abc123')->toArray();
        $this->assertSame('tok_abc123', $data['source']['id']);
    }

    #[Test]
    public function it_can_set_statement_descriptor(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->statementDescriptor('ACME Corp')
            ->toArray();

        $this->assertSame('ACME Corp', $data['statement_descriptor']);
    }

    #[Test]
    public function it_throws_exception_for_long_statement_descriptor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Statement descriptor must be 22 characters or less');

        $builder = $this->createBuilder();
        $builder->statementDescriptor('This is a very long statement descriptor that exceeds the limit');
    }

    #[Test]
    public function it_throws_exception_when_missing_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: amount, source');

        $builder = $this->createBuilder();
        $builder->create();
    }

    #[Test]
    public function it_can_reset_builder(): void
    {
        $builder = $this->createBuilder();
        $builder->amount(10000)->currency('KWD')->idempotent('test');

        $this->assertTrue($builder->has('amount'));
        $this->assertSame('test', $builder->get('reference')['idempotent']);

        $builder->reset();

        $this->assertFalse($builder->has('amount'));
        $this->assertNull($builder->get('reference'));
    }
}
