<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_validates_registration_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'A',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'The provided data is invalid.',
            ]);
    }

    /** @test */
    public function it_validates_login_data(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /** @test */
    public function it_validates_order_creation_data(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                [
                    'product_name' => '',
                    'quantity' => 0,
                    'unit_price' => -5.00,
                ],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /** @test */
    public function it_validates_payment_data(): void
    {
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/v1/payments/process', [
            'order_id' => 99999,
            'method' => 'invalid_method',
            'payment_data' => 'not_an_array',
        ], [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /** @test */
    public function it_validates_credit_card_data(): void
    {
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/v1/payments/process', [
            'order_id' => $order->id,
            'method' => 'credit_card',
            'payment_data' => [
                'card_number' => '1234567890',
                'card_exp_month' => 13,
                'card_exp_year' => 2020,
                'card_cvc' => '12',
            ],
        ], [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /** @test */
    public function it_accepts_valid_credit_card_data(): void
    {
        $order = \App\Models\Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => \App\Enums\OrderStatus::CONFIRMED,
        ]);

        $response = $this->postJson('/api/v1/payments/process', [
            'order_id' => $order->id,
            'method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'card_exp_month' => 12,
                'card_exp_year' => date('Y') + 2,
                'card_cvc' => '123',
            ],
        ], [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $this->assertNotEquals(422, $response->getStatusCode());
    }

    /** @test */
    public function it_validates_order_update_data(): void
    {
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/v1/orders/{$order->id}", [
            'status' => 'invalid_status',
            'items' => [
                [
                    'product_name' => str_repeat('A', 300),
                    'quantity' => 1001,
                    'unit_price' => 'not_a_number',
                ],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->token,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_provides_helpful_error_messages(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['success' => false])
            ->assertJsonFragment(['message' => 'The provided data is invalid.']);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors);
    }
}
