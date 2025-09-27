<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Services\Payments\Gateways\CreditCardGateway;
use App\Services\Payments\Gateways\PaypalGateway;
use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\PaymentGatewayFactory;
use Tests\TestCase;

class PaymentGatewayExtensibilityTest extends TestCase
{
    private PaymentGatewayFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = app(PaymentGatewayFactory::class);
    }

    /** @test */
    public function it_can_create_all_configured_payment_gateways(): void
    {
        $gateway = $this->factory->getGateway(PaymentMethod::CREDIT_CARD);
        $this->assertInstanceOf(CreditCardGateway::class, $gateway);

        $gateway = $this->factory->getGateway(PaymentMethod::PAYPAL);
        $this->assertInstanceOf(PaypalGateway::class, $gateway);

        try {
            $gateway = $this->factory->getGateway(PaymentMethod::STRIPE);
            $this->assertInstanceOf(StripeGateway::class, $gateway);
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Payment gateway not found', $e->getMessage());
        }
    }

    /** @test */
    public function it_provides_gateway_configuration_info(): void
    {
        $info = $this->factory->getGatewayInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('credit_card', $info);
        $this->assertArrayHasKey('paypal', $info);
        $this->assertArrayHasKey('stripe', $info);

        $stripeInfo = $info['stripe'];
        $this->assertArrayHasKey('name', $stripeInfo);
        $this->assertArrayHasKey('description', $stripeInfo);
        $this->assertArrayHasKey('enabled', $stripeInfo);
        $this->assertArrayHasKey('features', $stripeInfo);
        $this->assertArrayHasKey('currencies', $stripeInfo);
    }

    /** @test */
    public function it_filters_enabled_gateways_only(): void
    {
        $enabled = $this->factory->getEnabledGateways();

        $this->assertIsArray($enabled);

        $this->assertGreaterThanOrEqual(2, count($enabled));
        $this->assertArrayHasKey('credit_card', $enabled);
        $this->assertArrayHasKey('paypal', $enabled);
    }

    /** @test */
    public function it_configures_gateways_with_environment_variables(): void
    {
        $gateway = $this->factory->getGateway(PaymentMethod::STRIPE);

        $this->assertInstanceOf(StripeGateway::class, $gateway);

        $this->assertEquals('stripe', $gateway->getMethod());
    }

    /** @test */
    public function it_validates_gateway_methods_match_enums(): void
    {
        $creditCardGateway = $this->factory->getGateway(PaymentMethod::CREDIT_CARD);
        $this->assertEquals(PaymentMethod::CREDIT_CARD->value, $creditCardGateway->getMethod());

        $paypalGateway = $this->factory->getGateway(PaymentMethod::PAYPAL);
        $this->assertEquals(PaymentMethod::PAYPAL->value, $paypalGateway->getMethod());

        $stripeGateway = $this->factory->getGateway(PaymentMethod::STRIPE);
        $this->assertEquals(PaymentMethod::STRIPE->value, $stripeGateway->getMethod());
    }
}
