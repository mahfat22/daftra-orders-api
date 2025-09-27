<?php

namespace App\Contracts;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentServiceInterface
{
    /**
     * Get payments for the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaymentsForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Process a payment for an order.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function processPayment(Order $order, PaymentMethod $method, array $paymentData, int $userId): Payment;

    /**
     * Get payment by ID for a specific user.
     */
    public function getPaymentForUser(int $paymentId, int $userId): ?Payment;

    /**
     * Get payments for a specific order.
     */
    public function getPaymentsForOrder(Order $order, int $userId): Collection;

    /**
     * Get available payment methods.
     *
     * @return array<string>
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Check if payment method is supported.
     */
    public function isPaymentMethodSupported(PaymentMethod $method): bool;

    /**
     * Validate payment data for a specific method.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(PaymentMethod $method, array $paymentData): bool;
}
