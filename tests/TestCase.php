<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create a user and return JWT token for authentication.
     */
    protected function authenticateUser(?User $user = null): array
    {
        $user = $user ?: User::factory()->create();
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
    }

    /**
     * Create authenticated request headers.
     */
    protected function authHeaders(?User $user = null): array
    {
        return $this->authenticateUser($user)['headers'];
    }

    /**
     * Create a user with JWT token.
     */
    protected function createAuthenticatedUser(): array
    {
        return $this->authenticateUser();
    }

    /**
     * Assert JSON response structure matches expected format.
     */
    protected function assertApiResponse(array $response, bool $success = true): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertEquals($success, $response['success']);
        $this->assertArrayHasKey('message', $response);

        if ($success) {
            $this->assertArrayHasKey('data', $response);
        } else {
            $this->assertArrayHasKey('errors', $response);
        }
    }

    /**
     * Assert validation error response.
     */
    protected function assertValidationError(array $response, string $field): void
    {
        $this->assertApiResponse($response, false);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey($field, $response['errors']);
    }

    /**
     * Set up environment variables for testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['jwt.secret' => 'test_jwt_secret_key_for_testing_only']);

        config([
            'business.orders.max_per_day' => 100,
            'business.orders.min_amount' => 0.01,
            'business.orders.max_amount' => 50000.00,
            'business.payments.max_retries' => 3,
        ]);

        config([
            'payment_gateways.stripe.enabled' => true,
            'payment_gateways.paypal.enabled' => true,
            'payment_gateways.local.enabled' => true,
        ]);
    }
}
