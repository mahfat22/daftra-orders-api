<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PaymentServiceInterface;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private BusinessRulesService $businessRules,
        private PaymentGatewayFactory $gatewayFactory
    ) {}

    /**
     * Get payments for the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaymentsForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->getPaymentsForUser($userId, $filters, $perPage);
    }

    /**
     * Process a payment for an order.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function processPayment(Order $order, PaymentMethod $method, array $paymentData, int $userId): Payment
    {
        $this->businessRules->canCreatePayment($order, $userId);

        $gateway = $this->getGateway($method->value);

        if (! $gateway->validatePaymentData($paymentData)) {
            throw new InvalidArgumentException('Invalid payment data for '.$method->value.' gateway.');
        }

        $payment = $this->paymentRepository->create([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $order->total,
            'status' => PaymentStatus::PENDING,
        ]);

        try {
            $result = $gateway->processPayment($order, $paymentData);

            if ($result['success']) {
                $payment = $this->paymentRepository->update($payment, [
                    'status' => PaymentStatus::SUCCESSFUL,
                    'payment_id' => $result['payment_id'] ?? null,
                    'payload' => $result['payload'] ?? [],
                ]);
            } else {
                $payment = $this->paymentRepository->update($payment, [
                    'status' => PaymentStatus::FAILED,
                    'payload' => $result['payload'] ?? [],
                ]);
            }

            return $payment;

        } catch (\Exception $e) {
            $this->paymentRepository->update($payment, [
                'status' => PaymentStatus::FAILED,
                'payload' => [
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toISOString(),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Get payment by ID for a specific user.
     */
    public function getPaymentForUser(int $paymentId, int $userId): ?Payment
    {
        $payment = $this->paymentRepository->findByIdWithRelations($paymentId, ['order']);

        if (! $payment || ! $this->paymentRepository->belongsToUser($payment, $userId)) {
            return null;
        }

        return $payment;
    }

    /**
     * Get payments for a specific order.
     */
    public function getPaymentsForOrder(Order $order, int $userId): Collection
    {
        if ($order->user_id !== $userId) {
            throw new InvalidArgumentException('You do not have permission to view payments for this order');
        }

        return $this->paymentRepository->getPaymentsForOrder($order);
    }

    /**
     * Get available payment methods.
     *
     * @return array<string>
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->gatewayFactory->getAvailableMethods();
    }

    /**
     * Check if payment method is supported.
     */
    public function isPaymentMethodSupported(PaymentMethod $method): bool
    {
        return isset($this->gateways[$method->value]);
    }

    /**
     * Validate payment data for a specific method.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function validatePaymentData(PaymentMethod $method, array $paymentData): bool
    {
        $gateway = $this->getGateway($method->value);

        return $gateway->validatePaymentData($paymentData);
    }

    /**
     * Get the gateway for the specified payment method.
     */
    private function getGateway(string $method): PaymentGatewayInterface
    {
        return $this->gatewayFactory->getGateway($method);
    }
}
