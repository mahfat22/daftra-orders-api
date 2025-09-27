<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Process a payment for the given order.
     *
     * @param  array<string, mixed>  $paymentData
     * @return array{success: bool, payment_id?: string, message: string, payload?: array<string, mixed>}
     */
    public function processPayment(Order $order, array $paymentData): array;

    /**
     * Get the payment method identifier.
     */
    public function getMethod(): string;

    /**
     * Validate the payment data.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(array $paymentData): bool;
}
