<?php

namespace App\Repositories;

use App\Contracts\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get orders for a specific user with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getOrdersForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['items', 'payments'])
            ->where('user_id', $userId);

        if (isset($filters['status']) && in_array($filters['status'], OrderStatus::values())) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new order.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->fresh();
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * Find order by ID.
     */
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    /**
     * Find order by ID with relationships.
     *
     * @param  array<string>  $relations
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?Order
    {
        return Order::with($relations)->find($id);
    }

    /**
     * Check if order belongs to user.
     */
    public function belongsToUser(Order $order, int $userId): bool
    {
        return $order->user_id === $userId;
    }

    /**
     * Get orders by status.
     */
    public function getOrdersByStatus(OrderStatus $status, ?int $userId = null): Collection
    {
        $query = Order::where('status', $status->value);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Calculate total from order items.
     */
    public function calculateTotal(Order $order): float
    {
        return $order->items()->sum('total_price');
    }
}
