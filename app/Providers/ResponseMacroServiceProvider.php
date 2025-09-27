<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse {
            $response = [
                'success' => true,
                'message' => $message,
            ];

            if ($data !== null) {
                $response['data'] = $data;
            }

            return response()->json($response, $statusCode);
        });

        Response::macro('error', function (string $message = 'Error', $errors = null, int $statusCode = 400): JsonResponse {
            $response = [
                'success' => false,
                'message' => $message,
            ];

            if ($errors !== null) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $statusCode);
        });

        Response::macro('paginated', function ($data, string $message = 'Data retrieved successfully'): JsonResponse {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                    'has_more_pages' => $data->hasMorePages(),
                ],
            ]);
        });

        Response::macro('created', function ($data = null, string $message = 'Resource created successfully'): JsonResponse {
            return Response::success($data, $message, 201);
        });

        Response::macro('updated', function ($data = null, string $message = 'Resource updated successfully'): JsonResponse {
            return Response::success($data, $message, 200);
        });

        Response::macro('deleted', function (string $message = 'Resource deleted successfully', $data = null): JsonResponse {
            return Response::success($data, $message, 200);
        });

        Response::macro('notFound', function (string $message = 'Resource not found', $data = null): JsonResponse {
            return Response::error($message, $data, 404);
        });

        Response::macro('unauthorized', function (string $message = 'Unauthorized', $data = null): JsonResponse {
            return Response::error($message, $data, 401);
        });

        Response::macro('forbidden', function (string $message = 'Forbidden', $data = null): JsonResponse {
            return Response::error($message, $data, 403);
        });

        Response::macro('validationError', function ($data, string $message = 'Validation failed'): JsonResponse {
            return Response::error($message, $data, 422);
        });

        Response::macro('serverError', function (string $message = 'Internal server error', $data = null): JsonResponse {
            return Response::error($message, $data, 500);
        });
    }
}
