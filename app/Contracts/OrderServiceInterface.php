<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    /**
     * Get orders for the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getOrdersForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new order.
     *
     * @param  array<string, mixed>  $orderData
     */
    public function createOrder(array $orderData, int $userId): Order;

    /**
     * Get an order for a specific user.
     */
    public function getOrderForUser(int $orderId, int $userId): ?Order;

    /**
     * Update an existing order.
     *
     * @param  array<string, mixed>  $updateData
     */
    public function updateOrder(Order $order, array $updateData, int $userId): Order;

    /**
     * Delete an order.
     */
    public function deleteOrder(Order $order, int $userId): bool;

    /**
     * Confirm an order.
     */
    public function confirmOrder(Order $order, int $userId): Order;

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order, int $userId): Order;
}
