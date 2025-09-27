<?php

namespace App\Services;

use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private BusinessRulesService $businessRules
    ) {}

    /**
     * Get orders for the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getOrdersForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getOrdersForUser($userId, $filters, $perPage);
    }

    /**
     * Create a new order.
     *
     * @param  array<string, mixed>  $orderData
     */
    public function createOrder(array $orderData, int $userId): Order
    {
        try {
            DB::beginTransaction();

            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'status' => OrderStatus::PENDING,
                'total' => 0,
                'meta' => $orderData['meta'] ?? [],
            ]);

            $totalPrice = 0;

            foreach ($orderData['items'] as $item) {
                $itemTotalPrice = $item['quantity'] * $item['unit_price'];
                $totalPrice += $itemTotalPrice;

                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $itemTotalPrice,
                ]);
            }

            $this->orderRepository->update($order, ['total' => $totalPrice]);

            DB::commit();

            return $order->load(['items', 'user']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing order.
     *
     * @param  array<string, mixed>  $orderData
     */
    public function updateOrder(Order $order, array $orderData, int $userId): Order
    {
        $this->businessRules->canUpdateOrder($order, $userId);

        try {
            DB::beginTransaction();

            $updateData = [];
            if (isset($orderData['status'])) {
                $updateData['status'] = OrderStatus::from($orderData['status']);
            }
            if (isset($orderData['meta'])) {
                $updateData['meta'] = $orderData['meta'];
            }

            if (! empty($updateData)) {
                $this->orderRepository->update($order, $updateData);
            }

            if (isset($orderData['items'])) {
                $order->items()->delete();

                $totalPrice = 0;

                foreach ($orderData['items'] as $item) {
                    $itemTotalPrice = $item['quantity'] * $item['unit_price'];
                    $totalPrice += $itemTotalPrice;

                    $order->items()->create([
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $itemTotalPrice,
                    ]);
                }

                $this->orderRepository->update($order, ['total' => $totalPrice]);
            }

            DB::commit();

            return $order->fresh(['items', 'user']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an order.
     */
    public function deleteOrder(Order $order, int $userId): bool
    {
        $this->businessRules->canDeleteOrder($order, $userId);

        try {
            DB::beginTransaction();

            $order->items()->delete();

            $result = $this->orderRepository->delete($order);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get order by ID for a specific user.
     */
    public function getOrderForUser(int $orderId, int $userId): ?Order
    {
        $order = $this->orderRepository->findByIdWithRelations($orderId, ['items', 'payments', 'user']);

        if (! $order || ! $this->orderRepository->belongsToUser($order, $userId)) {
            return null;
        }

        return $order;
    }

    /**
     * Check if order can be updated.
     */
    public function canUpdateOrder(Order $order): bool
    {
        return $order->canBeUpdated();
    }

    /**
     * Check if order can be deleted.
     */
    public function canDeleteOrder(Order $order): bool
    {
        return $order->canBeDeleted();
    }

    /**
     * Confirm an order.
     */
    public function confirmOrder(Order $order, int $userId): Order
    {
        $this->businessRules->canConfirmOrder($order, $userId);

        return $this->orderRepository->update($order, [
            'status' => OrderStatus::CONFIRMED,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order, int $userId): Order
    {
        $this->businessRules->canCancelOrder($order, $userId);

        return $this->orderRepository->update($order, [
            'status' => OrderStatus::CANCELLED,
        ]);
    }
}
