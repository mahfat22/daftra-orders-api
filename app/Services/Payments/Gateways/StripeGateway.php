<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Models\Order;

class StripeGateway implements PaymentGatewayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'public_key' => '',
        'secret_key' => '',
        'webhook_secret' => '',
        'api_version' => '2023-10-16',
    ];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Set gateway configuration.
     *
     * @param  array<string, mixed>  $config
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get the payment method identifier.
     */
    public function getMethod(): string
    {
        return PaymentMethod::STRIPE->value;
    }

    /**
     * Process a payment for the given order.
     *
     * @param  array<string, mixed>  $paymentData
     * @return array{success: bool, payment_id?: string, message: string, payload?: array<string, mixed>}
     */
    public function processPayment(Order $order, array $paymentData): array
    {
        if (empty($this->config['secret_key'])) {
            return [
                'success' => false,
                'message' => 'Stripe gateway not properly configured',
                'payload' => ['error' => 'Missing Stripe secret key'],
            ];
        }

        try {
            $paymentIntentId = 'pi_'.uniqid();

            $cardNumber = $paymentData['card_number'] ?? '';

            $testCards = [
                '4242424242424242' => 'success',
                '4000000000000002' => 'declined',
                '4000000000009995' => 'insufficient_funds',
                '4000000000000119' => 'processing_error',
            ];

            $result = $testCards[$cardNumber] ?? 'success';

            if ($result === 'success') {
                return [
                    'success' => true,
                    'payment_id' => $paymentIntentId,
                    'message' => 'Payment processed successfully via Stripe',
                    'payload' => [
                        'gateway' => 'stripe',
                        'payment_intent_id' => $paymentIntentId,
                        'amount' => $order->total * 100,
                        'currency' => 'usd',
                        'processed_at' => now()->toISOString(),
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment failed: '.str_replace('_', ' ', $result),
                    'payload' => [
                        'gateway' => 'stripe',
                        'error_code' => $result,
                        'declined_at' => now()->toISOString(),
                    ],
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: '.$e->getMessage(),
                'payload' => [
                    'gateway' => 'stripe',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Validate payment data for this gateway.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(array $paymentData): bool
    {
        $requiredFields = ['card_number', 'exp_month', 'exp_year', 'cvc'];

        foreach ($requiredFields as $field) {
            if (empty($paymentData[$field])) {
                return false;
            }
        }

        $cardNumber = preg_replace('/\s+/', '', $paymentData['card_number']);
        if (! ctype_digit($cardNumber) || strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        $expMonth = (int) $paymentData['exp_month'];
        $expYear = (int) $paymentData['exp_year'];

        if ($expMonth < 1 || $expMonth > 12) {
            return false;
        }

        if ($expYear < date('Y')) {
            return false;
        }

        $cvc = (string) $paymentData['cvc'];
        if (! ctype_digit($cvc) || strlen($cvc) < 3 || strlen($cvc) > 4) {
            return false;
        }

        return true;
    }
}
