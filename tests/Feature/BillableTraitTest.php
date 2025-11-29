<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TapPay\Tap\Concerns\Billable;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Tests\TestCase;

class BillableTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->string('tap_customer_id')->nullable();
            $table->timestamps();
        });

        // Setup HTTP mocking
        $httpClient = $this->mockHttpClient();

        // Bind mocked client to container
        $this->app->instance(Client::class, $httpClient);

        // Rebind Tap singleton with mocked client
        $this->app->singleton('tap', function ($app) use ($httpClient) {
            return new \TapPay\Tap\Tap(
                $httpClient,
                $app->make(\TapPay\Tap\Contracts\MoneyContract::class)
            );
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

        $charge = $user->charge(5000, 'USD', [
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

        // deleteTapCustomer() returns void - no exception means success
        $user->deleteTapCustomer();

        $this->assertNull($user->fresh()->tap_customer_id);
    }
    #[Test]
    public function it_does_nothing_when_deleting_non_existent_customer(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // deleteTapCustomer() returns void - should not throw when no customer exists
        $user->deleteTapCustomer();

        // No exception thrown means success
        $this->assertNull($user->tap_customer_id);
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

        $this->expectException(\InvalidArgumentException::class);
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

        $builder = $user->newCharge(10000, 'KWD');

        $data = $builder->toArray();

        // KWD has 3 decimal places: 10000 / 1000 = 10.0
        $this->assertSame(10.0, $data['amount']);
        $this->assertSame('KWD', $data['currency']);
        $this->assertNotNull($user->fresh()->tap_customer_id);
    }

    #[Test]
    public function it_can_create_customer_with_phone_number(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_with_phone',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '965',
                'number' => '51234567',
            ],
        ])));

        $user = UserWithPhone::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '51234567',
            'phone_country_code' => '965',
        ]);

        $customer = $user->createAsTapCustomer();

        $this->assertSame('cus_with_phone', $customer->id());
        $this->assertSame('cus_with_phone', $user->fresh()->tap_customer_id);
    }

    #[Test]
    public function it_uses_default_currency_when_not_specified(): void
    {
        // Mock customer creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_default',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        // Mock charge creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_default_currency',
            'amount' => 25.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $charge = $user->charge(2500);

        $this->assertSame('chg_default_currency', $charge->id());
        $this->assertSame('USD', $charge->currency());
    }

    #[Test]
    public function it_uses_custom_currency_when_specified(): void
    {
        // Mock customer creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_custom',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        // Mock charge creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_custom_currency',
            'amount' => 25.00,
            'currency' => 'KWD',
            'status' => 'INITIATED',
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $charge = $user->charge(2500, 'KWD');

        $this->assertSame('chg_custom_currency', $charge->id());
        $this->assertSame('KWD', $charge->currency());
    }

    #[Test]
    public function it_does_not_create_duplicate_customer(): void
    {
        // Create user with existing customer ID
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_existing_123',
        ]);

        // Mock only charge creation (no customer creation)
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_no_duplicate',
            'amount' => 30.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'customer' => ['id' => 'cus_existing_123'],
        ])));

        $charge = $user->charge(3000);

        $this->assertSame('chg_no_duplicate', $charge->id());
        // Verify customer ID hasn't changed
        $this->assertSame('cus_existing_123', $user->fresh()->tap_customer_id);
    }

    #[Test]
    public function it_can_charge_with_additional_options(): void
    {
        // Mock customer creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_options',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        // Mock charge creation
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_with_options',
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'description' => 'Premium subscription',
            'metadata' => [
                'plan' => 'premium',
                'period' => 'monthly',
            ],
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $charge = $user->charge(10000, 'USD', [
            'description' => 'Premium subscription',
            'metadata' => [
                'plan' => 'premium',
                'period' => 'monthly',
            ],
            'source' => ['id' => 'src_card'],
        ]);

        $this->assertSame('chg_with_options', $charge->id());
        $this->assertSame(100.00, $charge->amount());
    }

    #[Test]
    public function it_can_create_customer_with_custom_options(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_custom_options',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'metadata' => [
                'role' => 'premium',
                'signup_date' => '2024-01-01',
            ],
        ])));

        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $customer = $user->createAsTapCustomer([
            'last_name' => 'Smith',
            'metadata' => [
                'role' => 'premium',
                'signup_date' => '2024-01-01',
            ],
        ]);

        $this->assertSame('cus_custom_options', $customer->id());
        $this->assertSame('Jane', $customer->firstName());
    }

    #[Test]
    public function it_uses_first_name_attribute_when_available(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_first_name',
            'first_name' => 'Robert',
            'email' => 'robert@example.com',
        ])));

        $user = UserWithFirstName::create([
            'first_name' => 'Robert',
            'email' => 'robert@example.com',
        ]);

        $customer = $user->createAsTapCustomer();

        $this->assertSame('cus_first_name', $customer->id());
        $this->assertSame('Robert', $customer->firstName());
    }

    #[Test]
    public function it_falls_back_to_guest_when_no_name_available(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_guest',
            'first_name' => 'Guest',
            'email' => 'anonymous@example.com',
        ])));

        $user = UserMinimal::create([
            'email' => 'anonymous@example.com',
        ]);

        $customer = $user->createAsTapCustomer();

        $this->assertSame('Guest', $customer->firstName());
    }

    #[Test]
    public function it_can_build_charge_with_source(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_builder_source',
            'first_name' => 'John Doe',
            'email' => 'john@example.com',
        ])));

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $builder = $user->newCharge(5000, 'USD');
        $data = $builder->source('src_card')->toArray();

        $this->assertSame('src_card', $data['source']['id']);
        $this->assertSame(50.0, $data['amount']);
    }

    #[Test]
    public function it_returns_tap_customer_id(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tap_customer_id' => 'cus_test_getter',
        ]);

        $this->assertSame('cus_test_getter', $user->tapCustomerId());
    }

    #[Test]
    public function it_sets_tap_customer_id(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user->setTapCustomerId('cus_test_setter');

        $this->assertSame('cus_test_setter', $user->fresh()->tap_customer_id);
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

/**
 * Test User model with phone support
 */
class UserWithPhone extends Model
{
    use Billable;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = true;
}

/**
 * Test User model with first_name attribute
 */
class UserWithFirstName extends Model
{
    use Billable;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = true;
}

/**
 * Test User model with minimal attributes
 */
class UserMinimal extends Model
{
    use Billable;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = true;
}