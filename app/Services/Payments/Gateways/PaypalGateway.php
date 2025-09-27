<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Models\Order;

class PaypalGateway implements PaymentGatewayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'client_id' => '',
        'client_secret' => '',
        'sandbox' => true,
        'api_url' => 'https://api-m.sandbox.paypal.com',
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
     * Process a payment for the given order.
     *
     * @param  array<string, mixed>  $paymentData
     * @return array{success: bool, payment_id?: string, message: string, payload?: array<string, mixed>}
     */
    public function processPayment(Order $order, array $paymentData): array
    {
        if (empty($this->config['client_id']) || empty($this->config['client_secret'])) {
            return [
                'success' => false,
                'message' => 'PayPal gateway not properly configured',
                'payload' => ['error' => 'Missing PayPal credentials'],
            ];
        }

        sleep(1);

        $email = $paymentData['paypal_email'] ?? '';
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Payment failed: Invalid PayPal email.',
                'payload' => [
                    'gateway' => 'paypal',
                    'timestamp' => now()->toISOString(),
                    'error_code' => 'INVALID_EMAIL',
                ],
            ];
        }

        if (random_int(1, 100) <= 3) {
            return [
                'success' => false,
                'message' => 'Payment failed: PayPal account has insufficient funds.',
                'payload' => [
                    'gateway' => 'paypal',
                    'timestamp' => now()->toISOString(),
                    'error_code' => 'INSUFFICIENT_FUNDS',
                ],
            ];
        }

        return [
            'success' => true,
            'payment_id' => 'pp_'.uniqid(),
            'message' => 'Payment processed successfully via PayPal.',
            'payload' => [
                'gateway' => 'paypal',
                'timestamp' => now()->toISOString(),
                'transaction_id' => 'pp_'.uniqid(),
                'payer_email' => $email,
            ],
        ];
    }

    /**
     * Get the payment method identifier.
     */
    public function getMethod(): string
    {
        return PaymentMethod::PAYPAL->value;
    }

    /**
     * Validate the payment data.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(array $paymentData): bool
    {
        return isset($paymentData['paypal_email']);
    }
}
