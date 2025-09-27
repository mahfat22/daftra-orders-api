<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gateways = ['stripe', 'paypal', 'cash_on_delivery'];
        $baseSlug = fake()->randomElement($gateways);
        $slug = $baseSlug.'_'.fake()->unique()->randomNumber(4);

        return [
            'name' => ucwords(str_replace('_', ' ', $baseSlug)),
            'slug' => $slug,
            'is_active' => fake()->boolean(80),
            'config' => $this->generateConfigForGateway($baseSlug),
        ];
    }

    /**
     * Create active gateway.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create inactive gateway.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create PayPal gateway.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PayPal',
            'slug' => 'paypal_'.fake()->unique()->randomNumber(4),
            'config' => [
                'client_id' => 'test_client_id_'.fake()->uuid(),
                'client_secret' => 'test_client_secret_'.fake()->uuid(),
                'environment' => 'sandbox',
            ],
        ]);
    }

    /**
     * Create Stripe gateway.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Stripe',
            'slug' => 'stripe_'.fake()->unique()->randomNumber(4),
            'config' => [
                'public_key' => 'pk_test_'.fake()->uuid(),
                'secret_key' => 'sk_test_'.fake()->uuid(),
                'environment' => 'test',
            ],
        ]);
    }

    /**
     * Create Cash on Delivery gateway.
     */
    public function cashOnDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cash on Delivery',
            'slug' => 'cash_on_delivery_'.fake()->unique()->randomNumber(4),
            'config' => [
                'instructions' => 'Pay when your order is delivered.',
                'enable_for_methods' => ['flat_rate'],
            ],
        ]);
    }

    /**
     * Generate configuration for specific gateway.
     */
    private function generateConfigForGateway(string $slug): array
    {
        return match ($slug) {
            'stripe' => [
                'public_key' => 'pk_test_'.fake()->uuid(),
                'secret_key' => 'sk_test_'.fake()->uuid(),
                'environment' => 'test',
            ],
            'paypal' => [
                'client_id' => fake()->uuid(),
                'client_secret' => fake()->uuid(),
                'environment' => 'sandbox',
            ],
            'cash_on_delivery' => [
                'instructions' => 'Pay when your order is delivered.',
                'enable_for_methods' => ['flat_rate', 'local_pickup'],
            ],
            default => [
                'api_key' => fake()->uuid(),
                'test_mode' => true,
            ],
        };
    }
}
