<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Container\Container;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Services\JsonResponseService;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        // Add exception types here if you don't want them to be reported
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Json response service
     *
     * @var JsonResponseService
     */
    protected $jsonResponseService;

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     * @param JsonResponseService $jsonResponseService
     */
    public function __construct(Container $container, JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
        parent::__construct($container);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            if (App::environment('local')) {
                // Detailed error response for local environment
                $statusCode = $this->getStatusCodeFromException($exception);
                if ($exception instanceof ValidationException) {
                    return response()->json([
                        'status' => false,
                        'message' => $exception->getMessage(), // You can add this for debugging or to display error message
                        'errors' => $exception->errors() // Optionally include a `data` key for consistency in your response structure
                    ], $statusCode);
                }
                if ($exception instanceof ModelNotFoundException) {
                    $modelName = class_basename($exception->getModel());
                    $ids = $exception->getIds();
                    return $this->jsonResponseService->fail([
                        'message' => "Invalid ID($ids[0]) $modelName object not found",
                        'exception' => (new \ReflectionClass($exception))->getShortName(),
                        'file' => $exception->getFile(),
                        "line" => $exception->getLine(),
                        'trace' => $exception->getTrace()
                    ], $statusCode);
                }

                return $this->jsonResponseService->fail([
                    'message' => json_decode($exception->getMessage()) ?: $exception->getMessage(),
                    'exception' => (new \ReflectionClass($exception))->getShortName(),
                    'file' => $exception->getFile(),
                    "line" => $exception->getLine(),
                    'trace' => $exception->getTrace()
                ], $statusCode);
            } else {

                // Standard error response for production environment
                $statusCode = $this->getStatusCodeFromException($exception);
                return $this->jsonResponseService->fail([
                    'message' => json_decode($exception->getMessage()) ?: $exception->getMessage(),
                ], $statusCode);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Get the HTTP status code based on the exception type.
     *
     * @param Throwable $exception
     * @return int
     */
    protected function getStatusCodeFromException(Throwable $exception): int
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return Response::HTTP_METHOD_NOT_ALLOWED;
        }

        if ($exception instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof ModelNotFoundException) {
            return Response::HTTP_NOT_FOUND;
        }

        if ($exception instanceof NotFoundHttpException) {
            return Response::HTTP_NOT_FOUND;
        }

        if ($exception instanceof UnauthorizedException) {
            return Response::HTTP_FORBIDDEN;
        }

        if ($exception instanceof ValidationException) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        if ($exception instanceof BadRequestException) {
            return Response::HTTP_BAD_REQUEST;
        }

        // Default to Internal Server Error
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Get the error message based on the exception type.
     *
     * @param Throwable $exception
     * @return string
     */
    protected function getErrorMessageFromException(Throwable $exception): string
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 'HTTP_METHOD_NOT_ALLOWED';
        }

        if ($exception instanceof AuthenticationException) {
            return 'HTTP_UNAUTHORIZED';
        }

        if ($exception instanceof ModelNotFoundException) {
            return 'MODEL_NOT_FOUND';
        }

        if ($exception instanceof NotFoundHttpException) {
            return 'HTTP_NOT_FOUND';
        }

        if ($exception instanceof UnauthorizedException) {
            return 'HTTP_FORBIDDEN';
        }

        if ($exception instanceof ValidationException) {
            return 'VALIDATION_ERROR';
        }

        if ($exception instanceof BadRequestException) {
            return 'HTTP_BAD_REQUEST';
        }

        return 'UNKNOWN_EXCEPTION';
    }
}
