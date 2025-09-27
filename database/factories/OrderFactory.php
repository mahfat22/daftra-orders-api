<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'total' => fake()->randomFloat(2, 10, 1000),
            'meta' => [
                'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
                'notes' => fake()->optional()->sentence(),
                'created_by' => 'api',
            ],
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CONFIRMED,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
        ]);
    }

    /**
     * Create order with specific total amount.
     */
    public function withTotal(float $total): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => $total,
        ]);
    }

    /**
     * Create order for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
