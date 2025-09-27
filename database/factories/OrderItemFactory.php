<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 1, 500);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'order_id' => Order::factory(),
            'product_name' => fake()->words(3, true).' '.fake()->randomElement(['Pro', 'Deluxe', 'Standard', 'Premium']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ];
    }

    /**
     * Create item for specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Create item with specific price.
     */
    public function withPrice(float $unitPrice, int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
        ]);
    }

    /**
     * Create expensive item.
     */
    public function expensive(): static
    {
        $unitPrice = fake()->randomFloat(2, 100, 2000);
        $quantity = fake()->numberBetween(1, 3);

        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
        ]);
    }

    /**
     * Create bulk item (high quantity).
     */
    public function bulk(): static
    {
        $unitPrice = fake()->randomFloat(2, 1, 50);
        $quantity = fake()->numberBetween(10, 100);

        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
        ]);
    }
}
