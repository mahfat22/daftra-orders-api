<?php

namespace App\Contracts;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    /**
     * Get orders for a specific user with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getOrdersForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new order.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order;

    /**
     * Update an order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order;

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool;

    /**
     * Find order by ID.
     */
    public function findById(int $id): ?Order;

    /**
     * Find order by ID with relationships.
     *
     * @param  array<string>  $relations
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?Order;

    /**
     * Check if order belongs to user.
     */
    public function belongsToUser(Order $order, int $userId): bool;

    /**
     * Get orders by status.
     */
    public function getOrdersByStatus(OrderStatus $status, ?int $userId = null): Collection;

    /**
     * Calculate total from order items.
     */
    public function calculateTotal(Order $order): float;
}
