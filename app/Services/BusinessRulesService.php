<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use InvalidArgumentException;

class BusinessRulesService
{
    /**
     * Validate if an order can be updated.
     */
    public function canUpdateOrder(Order $order, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        if ($order->hasPayments()) {
            throw new InvalidArgumentException('Orders with payments cannot be modified');
        }
    }

    /**
     * Validate if an order can be deleted.
     */
    public function canDeleteOrder(Order $order, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        if ($order->hasPayments()) {
            throw new InvalidArgumentException('Orders with payments cannot be deleted');
        }
    }

    /**
     * Validate if an order can be confirmed.
     */
    public function canConfirmOrder(Order $order, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        if ($order->status !== OrderStatus::PENDING) {
            throw new InvalidArgumentException('Only pending orders can be confirmed');
        }

        if ($order->items->isEmpty()) {
            throw new InvalidArgumentException('Orders must have at least one item to be confirmed');
        }
    }

    /**
     * Validate if an order can be canceled.
     */
    public function canCancelOrder(Order $order, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        if (! in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED])) {
            throw new InvalidArgumentException('Only pending or confirmed orders can be canceled');
        }

        if ($order->hasSuccessfulPayments()) {
            throw new InvalidArgumentException('Orders with successful payments cannot be canceled');
        }
    }

    /**
     * Validate if a payment can be created for an order.
     */
    public function canCreatePayment(Order $order, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        if ($order->status !== OrderStatus::CONFIRMED) {
            throw new InvalidArgumentException('Payments can only be made for confirmed orders');
        }

        if ($order->hasSuccessfulPayments()) {
            throw new InvalidArgumentException('This order has already been paid successfully');
        }

        if ($order->total <= 0) {
            throw new InvalidArgumentException('Cannot create payment for orders with zero or negative total');
        }
    }

    /**
     * Validate order status transition.
     */
    public function canTransitionOrderStatus(Order $order, OrderStatus $newStatus, int $userId): void
    {
        $this->validateOrderOwnership($order, $userId);

        $currentStatus = $order->status;

        $allowedTransitions = [
            OrderStatus::PENDING->value => [OrderStatus::CONFIRMED->value, OrderStatus::CANCELLED->value],
            OrderStatus::CONFIRMED->value => [OrderStatus::CANCELLED->value],
            OrderStatus::CANCELLED->value => [],
        ];

        if (! isset($allowedTransitions[$currentStatus->value])) {
            throw new InvalidArgumentException("Invalid current status: {$currentStatus->value}");
        }

        if (! in_array($newStatus->value, $allowedTransitions[$currentStatus->value])) {
            throw new InvalidArgumentException(
                "Cannot transition from {$currentStatus->value} to {$newStatus->value}"
            );
        }

        if ($newStatus === OrderStatus::CONFIRMED && $order->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot confirm an order without items');
        }

        if ($newStatus === OrderStatus::CANCELLED && $order->hasSuccessfulPayments()) {
            throw new InvalidArgumentException('Cannot cancel orders with successful payments');
        }
    }

    /**
     * Validate payment status transition.
     */
    public function canTransitionPaymentStatus(Payment $payment, PaymentStatus $newStatus): void
    {
        $currentStatus = $payment->status;

        $allowedTransitions = [
            PaymentStatus::PENDING->value => [PaymentStatus::SUCCESSFUL->value, PaymentStatus::FAILED->value],
            PaymentStatus::SUCCESSFUL->value => [],
            PaymentStatus::FAILED->value => [PaymentStatus::PENDING->value],
        ];

        if (! isset($allowedTransitions[$currentStatus->value])) {
            throw new InvalidArgumentException("Invalid current payment status: {$currentStatus->value}");
        }

        if (! in_array($newStatus->value, $allowedTransitions[$currentStatus->value])) {
            throw new InvalidArgumentException(
                "Cannot transition payment from {$currentStatus->value} to {$newStatus->value}"
            );
        }
    }

    /**
     * Validate order ownership.
     */
    private function validateOrderOwnership(Order $order, int $userId): void
    {
        if ($order->user_id !== $userId) {
            throw new InvalidArgumentException('You do not have permission to access this order');
        }
    }

    /**
     * Get order and validate ownership in one step.
     */
    public function getOrderForUser(int $orderId, int $userId): Order
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw new InvalidArgumentException('Order not found');
        }

        $this->validateOrderOwnership($order, $userId);

        return $order;
    }

    /**
     * Validate if order total matches sum of items.
     */
    public function validateOrderTotal(Order $order): void
    {
        $calculatedTotal = $order->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        if (abs($order->total - $calculatedTotal) > 0.01) {
            throw new InvalidArgumentException(
                'Order total does not match sum of items. Expected: '.$calculatedTotal.', Got: '.$order->total
            );
        }
    }

    /**
     * Validate payment amount matches order total.
     */
    public function validatePaymentAmount(Payment $payment, Order $order): void
    {
        if (abs($payment->amount - $order->total) > 0.01) {
            throw new InvalidArgumentException(
                'Payment amount must match order total. Expected: '.$order->total.', Got: '.$payment->amount
            );
        }
    }
}
