<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\OrderFilter;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Requests\Api\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private OrderServiceInterface $orderService
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $orderFilter = new OrderFilter($request);
        $filters = $orderFilter->getActiveFilters();

        $perPage = min($request->get('per_page', 15), 100);
        $orders = $this->orderService->getOrdersForUser($userId, $filters, $perPage);

        return Response::success(
            OrderResource::collection($orders),
            'Orders retrieved successfully'
        );
    }

    /**
     * Store a newly created order.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $validated = $request->validated();
            $orderData = [
                'items' => $validated['items'],
                'meta' => $validated['meta'] ?? [],
            ];

            $order = $this->orderService->createOrder($orderData, $userId);

            return Response::created(
                new OrderResource($order->load(['items', 'payments'])),
                'Order created successfully'
            );

        } catch (ValidationException $e) {
            return Response::validationError('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return Response::serverError('Failed to create order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $userId = auth('api')->id();
        $order = $this->orderService->getOrderForUser($id, $userId);

        return Response::success(
            new OrderResource($order),
            'Order retrieved successfully'
        );
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $order = $this->orderService->getOrderForUser($id, $userId);
            $validated = $request->validated();
            $updateData = [];

            if (isset($validated['status']) && OrderStatus::tryFrom($validated['status'])) {
                $updateData['status'] = OrderStatus::from($validated['status']);
            }

            if (isset($validated['meta'])) {
                $updateData['meta'] = $validated['meta'];
            }

            if (isset($validated['items'])) {
                $updateData['items'] = $validated['items'];
            }

            $updatedOrder = $this->orderService->updateOrder($order, $updateData, $userId);

            return Response::updated(
                new OrderResource($updatedOrder),
                'Order updated successfully'
            );

        } catch (ValidationException $e) {
            return Response::validationError('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return Response::serverError('Failed to update order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $order = $this->orderService->getOrderForUser($id, $userId);
            $this->orderService->deleteOrder($order, $userId);

            return Response::deleted('Order deleted successfully');

        } catch (\Exception $e) {
            return Response::serverError('Failed to delete order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm the specified order.
     */
    public function confirm(int $id): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $order = $this->orderService->getOrderForUser($id, $userId);
            $confirmedOrder = $this->orderService->confirmOrder($order, $userId);

            return Response::success(
                new OrderResource($confirmedOrder),
                'Order confirmed successfully'
            );

        } catch (\Exception $e) {
            return Response::serverError('Failed to confirm order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $order = $this->orderService->getOrderForUser($id, $userId);
            $canceledOrder = $this->orderService->cancelOrder($order, $userId);

            return Response::success(
                new OrderResource($canceledOrder),
                'Order canceled successfully'
            );

        } catch (\Exception $e) {
            return Response::serverError('Failed to cancel order', ['error' => $e->getMessage()]);
        }
    }
}
