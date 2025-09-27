<?php

namespace App\Repositories;

use App\Contracts\PaymentRepositoryInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * Get payments for a specific user with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaymentsForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['order'])
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new payment.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Update a payment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh();
    }

    /**
     * Find payment by ID.
     */
    public function findById(int $id): ?Payment
    {
        return Payment::find($id);
    }

    /**
     * Find payment by ID with relationships.
     *
     * @param  array<string>  $relations
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?Payment
    {
        return Payment::with($relations)->find($id);
    }

    /**
     * Get payments for a specific order.
     */
    public function getPaymentsForOrder(Order $order): Collection
    {
        return $order->payments()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get payments by status.
     */
    public function getPaymentsByStatus(PaymentStatus $status, ?int $userId = null): Collection
    {
        $query = Payment::where('status', $status->value);

        if ($userId) {
            $query->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        return $query->get();
    }

    /**
     * Check if payment belongs to user.
     */
    public function belongsToUser(Payment $payment, int $userId): bool
    {
        return $payment->order->user_id === $userId;
    }

    /**
     * Get successful payments for an order.
     */
    public function getSuccessfulPaymentsForOrder(Order $order): Collection
    {
        return $order->payments()
            ->where('status', PaymentStatus::SUCCESSFUL->value)
            ->get();
    }
}
