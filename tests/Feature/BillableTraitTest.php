<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TapPay\Tap\Concerns\Billable;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Tests\TestCase;

class BillableTraitTest extends TestCase
{
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('tap_customer_id')->nullable();
            $table->timestamps();
        });

        // Setup HTTP mocking
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $httpClient = new Client(config('tap.secret_key'));
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $guzzleClient);

        // Bind mocked client to container
        $this->app->instance(Client::class, $httpClient);

        // Rebind Tap singleton with mocked client
        $this->app->singleton('tap', function ($app) use ($httpClient) {
            $tap = new \TapPay\Tap\Tap();
            $reflection = new \ReflectionClass($tap);
            $property = $reflection->getProperty('client');
            $property->setAccessible(true);
            $property->setValue($tap, $httpClient);
            return $tap;
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }
    #[Test]
    public function it_can_create_tap_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $customer = $user->createAsTapCustomer();

        $this->assertSame('cus_test_123', $customer->id());
        $this->assertSame('cus_test_123', $user->fresh()->tap_customer_id);
    }
    #[Test]
    public function it_can_charge_billable_model(): void
    {
        // Mock customer creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        // Mock charge creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_456',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'customer' => ['id' => 'cus_test_123'],
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $charge = $user->charge(50.00, 'USD', [
            'source' => ['id' => 'src_card'],
        ]);

        $this->assertSame('chg_test_456', $charge->id());
        $this->assertSame(50.00, $charge->amount());
        $this->assertNotNull($user->fresh()->tap_customer_id);
    }
    #[Test]
    public function it_can_retrieve_tap_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_test_123',
        ]);

        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        $customer = $user->asTapCustomer();

        $this->assertNotNull($customer);
        $this->assertSame('cus_test_123', $customer->id());
    }
    #[Test]
    public function it_returns_null_when_no_tap_customer_exists(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertNull($user->asTapCustomer());
    }
    #[Test]
    public function it_can_update_tap_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_test_123',
        ]);

        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123',
            'first_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])));

        $customer = $user->updateTapCustomer([
            'first_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->assertSame('Jane Doe', $customer->firstName());
    }
    #[Test]
    public function it_creates_customer_if_updating_non_existent_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_new_123',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        $customer = $user->updateTapCustomer([
            'first_name' => 'John Doe',
        ]);

        $this->assertSame('cus_new_123', $customer->id());
        $this->assertSame('cus_new_123', $user->fresh()->tap_customer_id);
    }
    #[Test]
    public function it_can_delete_tap_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_test_123',
        ]);

        $this->mockHandler->append(new Response(200, [], json_encode([
            'deleted' => true,
        ])));

        $result = $user->deleteTapCustomer();

        $this->assertTrue($result);
        $this->assertNull($user->fresh()->tap_customer_id);
    }
    #[Test]
    public function it_returns_false_when_deleting_non_existent_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $result = $user->deleteTapCustomer();

        $this->assertFalse($result);
    }
    #[Test]
    public function it_can_create_card_token(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_test_123',
        ]);

        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_test_789',
            'card' => 'card_abc',
            'customer' => 'cus_test_123',
        ])));

        $token = $user->createCardToken('card_abc');

        $this->assertSame('tok_test_789', $token->id());
    }
    #[Test]
    public function it_throws_exception_when_creating_token_without_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Customer must be created in Tap first');

        $user->createCardToken('card_abc');
    }
    #[Test]
    public function it_can_build_charge_with_fluent_interface(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $builder = $user->newCharge(100.00, 'KWD');

        $data = $builder->toArray();

        $this->assertSame(100.00, $data['amount']);
        $this->assertSame('KWD', $data['currency']);
        $this->assertNotNull($user->fresh()->tap_customer_id);
    }
}

/**
 * Test User model with Billable trait
 */
class User extends Model
{
    use Billable;

    protected $guarded = [];
    public $timestamps = true;
}