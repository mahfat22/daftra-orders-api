<?php

namespace App\Contracts;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    /**
     * Get payments for a specific user with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaymentsForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new payment.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment;

    /**
     * Update a payment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Payment $payment, array $data): Payment;

    /**
     * Find payment by ID.
     */
    public function findById(int $id): ?Payment;

    /**
     * Find payment by ID with relationships.
     *
     * @param  array<string>  $relations
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?Payment;

    /**
     * Get payments for a specific order.
     */
    public function getPaymentsForOrder(Order $order): Collection;

    /**
     * Get payments by status.
     */
    public function getPaymentsByStatus(PaymentStatus $status, ?int $userId = null): Collection;

    /**
     * Check if payment belongs to user.
     */
    public function belongsToUser(Payment $payment, int $userId): bool;

    /**
     * Get successful payments for an order.
     */
    public function getSuccessfulPaymentsForOrder(Order $order): Collection;
}
