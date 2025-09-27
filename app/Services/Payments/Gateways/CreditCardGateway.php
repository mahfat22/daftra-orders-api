<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Models\Order;

class CreditCardGateway implements PaymentGatewayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'api_key' => '',
        'secret' => '',
        'sandbox' => true,
        'api_url' => 'https://api-sandbox.creditcard.com/v1',
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
        if (empty($this->config['api_key']) || empty($this->config['secret'])) {
            return [
                'success' => false,
                'message' => 'Credit card gateway not properly configured',
                'payload' => ['error' => 'Missing API credentials'],
            ];
        }

        sleep(1);

        $cardNumber = $paymentData['card_number'] ?? '';
        if (! $this->isValidCardNumber($cardNumber)) {
            return [
                'success' => false,
                'message' => 'Payment failed: Invalid card number.',
                'payload' => [
                    'gateway' => 'credit_card',
                    'timestamp' => now()->toISOString(),
                    'error_code' => 'INVALID_CARD',
                ],
            ];
        }

        if (random_int(1, 100) <= 5) {
            return [
                'success' => false,
                'message' => 'Payment failed: Card declined.',
                'payload' => [
                    'gateway' => 'credit_card',
                    'timestamp' => now()->toISOString(),
                    'error_code' => 'CARD_DECLINED',
                ],
            ];
        }

        return [
            'success' => true,
            'payment_id' => 'cc_'.uniqid(),
            'message' => 'Payment processed successfully via Credit Card.',
            'payload' => [
                'gateway' => 'credit_card',
                'timestamp' => now()->toISOString(),
                'transaction_id' => 'cc_'.uniqid(),
                'card_last_four' => substr($cardNumber, -4),
            ],
        ];
    }

    /**
     * Get the payment method identifier.
     */
    public function getMethod(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }

    /**
     * Validate the payment data.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(array $paymentData): bool
    {
        return isset($paymentData['card_number'], $paymentData['cvv'], $paymentData['expiry_month'], $paymentData['expiry_year']);
    }

    /**
     * Simple card number validation.
     */
    private function isValidCardNumber(string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        return $this->luhnCheck($cardNumber);
    }

    /**
     * Luhn algorithm for card validation.
     */
    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $alternate = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = intval($number[$i]);

            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n = ($n % 10) + 1;
                }
            }

            $sum += $n;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }
}
