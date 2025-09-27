<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PaymentServiceInterface;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Filters\PaymentFilter;
use App\Http\Requests\Api\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    /**
     * Display a listing of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $paymentFilter = new PaymentFilter($request);
        $filters = $paymentFilter->getActiveFilters();

        $perPage = min($request->get('per_page', 15), 100);
        $payments = $this->paymentService->getPaymentsForUser($userId, $filters, $perPage);

        return Response::success(
            PaymentResource::collection($payments),
            'Payments retrieved successfully'
        );
    }

    /**
     * Process a payment for an order.
     */
    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $userId = auth('api')->id();

            $order = Order::findOrFail($request->validated()['order_id']);

            if (! PaymentMethod::tryFrom($request->validated()['method'])) {
                return Response::validationError('Invalid payment method');
            }

            $paymentMethod = PaymentMethod::from($request->validated()['method']);

            $payment = $this->paymentService->processPayment(
                $order,
                $paymentMethod,
                $request->validated()['payment_data'],
                $userId
            );

            return Response::created(
                new PaymentResource($payment->load(['order'])),
                'Payment processed successfully'
            );

        } catch (ValidationException $e) {
            return Response::validationError('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return Response::serverError('Payment processing failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(int $id): JsonResponse
    {
        $userId = auth('api')->id();
        $payment = $this->paymentService->getPaymentForUser($id, $userId);

        return Response::success(
            new PaymentResource($payment),
            'Payment retrieved successfully'
        );
    }

    /**
     * Get payments for a specific order.
     */
    public function orderPayments(int $orderId): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $order = Order::findOrFail($orderId);

            $payments = $this->paymentService->getPaymentsForOrder($order, $userId);

            return Response::success(
                PaymentResource::collection($payments),
                'Order payments retrieved successfully'
            );

        } catch (\Exception $e) {
            return Response::serverError('Failed to retrieve order payments', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get available payment methods.
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = $this->paymentService->getAvailablePaymentMethods();

        return Response::success(
            $methods,
            'Available payment methods retrieved successfully'
        );
    }
}
