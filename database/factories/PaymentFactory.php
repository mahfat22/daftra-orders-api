<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method = fake()->randomElement(PaymentMethod::cases());

        return [
            'payment_id' => fake()->unique()->uuid(),
            'order_id' => Order::factory(),
            'status' => fake()->randomElement(PaymentStatus::cases()),
            'method' => $method,
            'amount' => fake()->randomFloat(2, 10, 1000),
            'payload' => $this->generatePayloadForMethod($method),
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the payment is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::SUCCESSFUL,
            'payload' => array_merge(
                $attributes['payload'] ?? [],
                [
                    'transaction_id' => fake()->uuid(),
                    'completed_at' => now()->toISOString(),
                ]
            ),
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
            'payload' => array_merge(
                $attributes['payload'] ?? [],
                [
                    'error_code' => fake()->randomElement(['card_declined', 'insufficient_funds', 'invalid_card']),
                    'error_message' => fake()->sentence(),
                    'failed_at' => now()->toISOString(),
                ]
            ),
        ]);
    }

    /**
     * Create payment for specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);
    }

    /**
     * Create payment with specific method.
     */
    public function withMethod(PaymentMethod $method): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => $method,
            'payload' => $this->generatePayloadForMethod($method),
        ]);
    }

    /**
     * Create credit card payment.
     */
    public function creditCard(): static
    {
        return $this->withMethod(PaymentMethod::CREDIT_CARD);
    }

    /**
     * Create PayPal payment.
     */
    public function paypal(): static
    {
        return $this->withMethod(PaymentMethod::PAYPAL);
    }

    /**
     * Create high amount payment.
     */
    public function highAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, 1000, 10000),
        ]);
    }

    /**
     * Generate payload based on payment method.
     */
    private function generatePayloadForMethod(PaymentMethod $method): array
    {
        return match ($method) {
            PaymentMethod::CREDIT_CARD => [
                'card_last_four' => fake()->numerify('****'),
                'card_brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
                'cardholder_name' => fake()->name(),
            ],
            PaymentMethod::PAYPAL => [
                'paypal_email' => fake()->safeEmail(),
                'paypal_transaction_id' => fake()->uuid(),
            ],
            PaymentMethod::STRIPE => [
                'stripe_charge_id' => 'ch_'.fake()->uuid(),
                'stripe_customer_id' => 'cus_'.fake()->uuid(),
            ],
        };
    }
}
