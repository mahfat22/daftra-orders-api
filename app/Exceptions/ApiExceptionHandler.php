<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Map of exception classes to their handler methods
     */
    public static array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        AccessDeniedHttpException::class => 'handleAuthenticationException',
        AuthorizationException::class => 'handleAuthorizationException',
        ValidationException::class => 'handleValidationException',
        ModelNotFoundException::class => 'handleNotFoundException',
        NotFoundHttpException::class => 'handleNotFoundException',
        MethodNotAllowedHttpException::class => 'handleMethodNotAllowedException',
        HttpException::class => 'handleHttpException',
        QueryException::class => 'handleQueryException',
        InvalidArgumentException::class => 'handleInvalidArgumentException',
        \TypeError::class => 'handleTypeErrorException',
    ];

    /**
     * Handle authentication exceptions
     */
    public static function handleAuthenticationException(
        AuthenticationException|AccessDeniedHttpException $e,
        Request $request
    ): JsonResponse {
        $debug = self::getDebugData($e);

        return response()->unauthorized(
            'Authentication required. Please provide valid credentials.',
            $debug
        );
    }

    /**
     * Handle authorization exceptions
     */
    public static function handleAuthorizationException(
        AuthorizationException $e,
        Request $request
    ): JsonResponse {
        $debug = self::getDebugData($e);

        return response()->forbidden(
            'You do not have permission to perform this action.',
            $debug
        );
    }

    /**
     * Handle validation exceptions
     */
    public static function handleValidationException(
        ValidationException $e,
        Request $request
    ): JsonResponse {
        $errors = [];

        foreach ($e->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        $debug = self::getDebugData($e);

        return response()->validationError(
            $errors,
            'The provided data is invalid.'
        );
    }

    /**
     * Handle not found exceptions
     */
    public static function handleNotFoundException(
        ModelNotFoundException|NotFoundHttpException $e,
        Request $request
    ): JsonResponse {
        $message = $e instanceof ModelNotFoundException
            ? 'The requested resource was not found.'
            : "The requested endpoint '{$request->getRequestUri()}' was not found.";

        $debug = self::getDebugData($e);

        return response()->notFound($message, $debug);
    }

    /**
     * Handle method not allowed exceptions
     */
    public static function handleMethodNotAllowedException(
        MethodNotAllowedHttpException $e,
        Request $request
    ): JsonResponse {
        $allowedMethods = isset($e->getHeaders()['Allow'])
            ? explode(', ', $e->getHeaders()['Allow'])
            : [];

        $message = "The {$request->method()} method is not allowed for this endpoint.";
        $debug = self::getDebugData($e);

        return response()->methodNotAllowed($message, $allowedMethods, $debug);
    }

    /**
     * Handle general HTTP exceptions
     */
    public static function handleHttpException(HttpException $e, Request $request): JsonResponse
    {
        $message = $e->getMessage() ?: 'An HTTP error occurred.';
        $debug = self::getDebugData($e);

        return match ($e->getStatusCode()) {
            401 => response()->unauthorized($message, $debug),
            403 => response()->forbidden($message, $debug),
            404 => response()->notFound($message, $debug),
            405 => response()->methodNotAllowed($message, [], $debug),
            409 => response()->conflict($message, null, $debug),
            422 => response()->validationError($message, null, 422, $debug),
            500 => response()->serverError($message, null, $debug),
            default => response()->error($message, $e->getStatusCode(), null, $debug),
        };
    }

    /**
     * Handle database query exceptions
     */
    public static function handleQueryException(QueryException $e, Request $request): JsonResponse
    {
        $errorCode = $e->errorInfo[1] ?? null;
        $debug = self::getDebugData($e, ['error_code' => $errorCode, 'sql' => $e->getSql()]);

        return match ($errorCode) {
            1451 => response()->conflict(
                'Cannot delete this resource because it is referenced by other records.',
                null,
                $debug
            ),
            1062 => response()->conflict(
                'A record with this information already exists.',
                null,
                $debug
            ),
            default => response()->serverError(
                'A database error occurred. Please try again later.',
                null,
                $debug
            ),
        };
    }

    /**
     * Handle invalid argument exceptions (business rule violations)
     */
    public static function handleInvalidArgumentException(
        InvalidArgumentException $e,
        Request $request
    ): JsonResponse {
        $debug = self::getDebugData($e);

        return response()->json([
            'success' => false,
            'message' => 'Business rule violation',
            'errors' => ['business_rule' => [$e->getMessage()]],
            'data' => null,
        ], 422);
    }

    /**
    * Handle type error exceptions
    */
    public static function handleTypeErrorException(
        \TypeError $e,
        Request $request
    ): JsonResponse {
        $debug = self::getDebugData($e);

        return response()->json([
            'success' => false,
            'message' => 'Type error occurred',
            'errors' => ['type' => [$e->getMessage()]],
            'data' => null,
        ], 500);
    }

    /**
     * Handle general exceptions with fallback
     */
    public static function handleGeneralException(Throwable $e, Request $request): JsonResponse
    {
        $debug = self::getDebugData($e);

        if (config('app.debug')) {
            return response()->error(
                $e->getMessage() ?: 'An unexpected error occurred.',
                500,
                null,
                $debug
            );
        }

        return response()->serverError(
            'An unexpected error occurred. Please try again later.'
        );
    }

    /**
     * Generate debug data for exceptions when debug mode is enabled
     */
    private static function getDebugData(Throwable $e, array $additionalData = []): ?array
    {
        if (! config('app.debug')) {
            return null;
        }

        return array_merge([
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'timestamp' => now()->toISOString(),
        ], $additionalData);
    }
}
