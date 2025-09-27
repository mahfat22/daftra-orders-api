<?php

namespace Tests\Unit;

use App\Models\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_gateway_can_be_created(): void
    {
        $gateway = PaymentGateway::factory()->create();

        $this->assertInstanceOf(PaymentGateway::class, $gateway);
        $this->assertDatabaseHas('payment_gateways', [
            'id' => $gateway->id,
            'name' => $gateway->name,
        ]);
    }

    public function test_payment_gateway_config_array_casting(): void
    {
        $configData = [
            'api_key' => 'test_key_123',
            'secret_key' => 'test_secret_456',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.com/webhook',
        ];

        $gateway = PaymentGateway::factory()->create([
            'config' => $configData,
        ]);

        $this->assertIsArray($gateway->config);
        $this->assertEquals($configData, $gateway->config);
        $this->assertEquals('test_key_123', $gateway->config['api_key']);
        $this->assertEquals('sandbox', $gateway->config['environment']);
    }

    public function test_payment_gateway_is_active_boolean_casting(): void
    {
        $activeGateway = PaymentGateway::factory()->create([
            'is_active' => true,
        ]);

        $inactiveGateway = PaymentGateway::factory()->create([
            'is_active' => false,
        ]);

        $this->assertIsBool($activeGateway->is_active);
        $this->assertTrue($activeGateway->is_active);

        $this->assertIsBool($inactiveGateway->is_active);
        $this->assertFalse($inactiveGateway->is_active);
    }

    public function test_payment_gateway_fillable_attributes(): void
    {
        $gateway = new PaymentGateway;
        $fillable = $gateway->getFillable();

        $expectedFillable = ['name', 'slug', 'config', 'is_active'];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_payment_gateway_factory_states(): void
    {
        $activeGateway = PaymentGateway::factory()->active()->create();
        $this->assertTrue($activeGateway->is_active);

        $inactiveGateway = PaymentGateway::factory()->inactive()->create();
        $this->assertFalse($inactiveGateway->is_active);

        $paypalGateway = PaymentGateway::factory()->paypal()->create();
        $this->assertEquals('PayPal', $paypalGateway->name);
        $this->assertStringStartsWith('paypal_', $paypalGateway->slug);
        $this->assertArrayHasKey('client_id', $paypalGateway->config);

        $stripeGateway = PaymentGateway::factory()->stripe()->create();
        $this->assertEquals('Stripe', $stripeGateway->name);
        $this->assertStringStartsWith('stripe_', $stripeGateway->slug);
        $this->assertArrayHasKey('public_key', $stripeGateway->config);

        $codGateway = PaymentGateway::factory()->cashOnDelivery()->create();
        $this->assertEquals('Cash on Delivery', $codGateway->name);
        $this->assertStringStartsWith('cash_on_delivery_', $codGateway->slug);
    }

    public function test_payment_gateway_timestamps(): void
    {
        $gateway = PaymentGateway::factory()->create();

        $this->assertNotNull($gateway->created_at);
        $this->assertNotNull($gateway->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gateway->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gateway->updated_at);
    }

    public function test_payment_gateway_can_be_updated(): void
    {
        $gateway = PaymentGateway::factory()->inactive()->create([
            'name' => 'Original Gateway',
        ]);

        $gateway->update([
            'name' => 'Updated Gateway',
            'is_active' => true,
        ]);

        $this->assertEquals('Updated Gateway', $gateway->name);
        $this->assertTrue($gateway->is_active);
        $this->assertDatabaseHas('payment_gateways', [
            'id' => $gateway->id,
            'name' => 'Updated Gateway',
            'is_active' => true,
        ]);
    }

    public function test_payment_gateway_can_be_deleted(): void
    {
        $gateway = PaymentGateway::factory()->create();
        $gatewayId = $gateway->id;

        $gateway->delete();

        $this->assertDatabaseMissing('payment_gateways', [
            'id' => $gatewayId,
        ]);
    }

    public function test_payment_gateway_unique_slug(): void
    {
        $gateway1 = PaymentGateway::factory()->create(['slug' => 'unique_gateway_1']);
        $gateway2 = PaymentGateway::factory()->create(['slug' => 'unique_gateway_2']);

        $this->assertNotEquals($gateway1->slug, $gateway2->slug);
        $this->assertEquals('unique_gateway_1', $gateway1->slug);
        $this->assertEquals('unique_gateway_2', $gateway2->slug);
    }

    public function test_payment_gateway_with_empty_config(): void
    {
        $gateway = PaymentGateway::factory()->create([
            'config' => [],
        ]);

        $this->assertIsArray($gateway->config);
        $this->assertEmpty($gateway->config);
    }

    public function test_payment_gateway_with_complex_config(): void
    {
        $complexConfig = [
            'api_credentials' => [
                'public_key' => 'pk_test_123456',
                'secret_key' => 'sk_test_789012',
            ],
            'settings' => [
                'environment' => 'sandbox',
                'currency' => 'USD',
                'auto_capture' => true,
            ],
            'webhooks' => [
                'endpoint_url' => 'https://example.com/stripe/webhook',
                'events' => ['payment_intent.succeeded', 'payment_intent.payment_failed'],
            ],
        ];

        $gateway = PaymentGateway::factory()->create([
            'config' => $complexConfig,
        ]);

        $this->assertIsArray($gateway->config);
        $this->assertEquals('pk_test_123456', $gateway->config['api_credentials']['public_key']);
        $this->assertEquals('sandbox', $gateway->config['settings']['environment']);
        $this->assertTrue($gateway->config['settings']['auto_capture']);
        $this->assertContains('payment_intent.succeeded', $gateway->config['webhooks']['events']);
    }

    public function test_payment_gateway_scopes_active(): void
    {
        PaymentGateway::factory()->active()->count(3)->create();
        PaymentGateway::factory()->inactive()->count(2)->create();

        $activeGateways = PaymentGateway::where('is_active', true)->get();
        $inactiveGateways = PaymentGateway::where('is_active', false)->get();

        $this->assertEquals(3, $activeGateways->count());
        $this->assertEquals(2, $inactiveGateways->count());

        foreach ($activeGateways as $gateway) {
            $this->assertTrue($gateway->is_active);
        }

        foreach ($inactiveGateways as $gateway) {
            $this->assertFalse($gateway->is_active);
        }
    }

    public function test_payment_gateway_config_validation(): void
    {
        $gateway = PaymentGateway::factory()->create([
            'config' => null,
        ]);

        $this->assertIsArray($gateway->config);
        $this->assertEmpty($gateway->config);
    }
}
