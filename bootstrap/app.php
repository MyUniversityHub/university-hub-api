<?php

use App\Http\Middleware\roleAuthAPI;
use App\Http\Middleware\RoleMiddleware;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            ROLE_PERMISSION_MIDDLEWARE => roleAuthAPI::class,
            ROLE_MIDDLEWARE => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            Log::channel('error')->error('Common Log Error Message: ', [$e]);
            $response = new class {
                use ApiResponse;
            };

            // Exception Validate
            if ($e instanceof ValidationException) {
                return $response->validationErrorResponse($e->errors(), $e->getMessage());
            }

            // Exception Authenticate
            if ($e instanceof AuthenticationException) {
                return $response->errorResponse('Access denied', Response::HTTP_UNAUTHORIZED, 'Unauthorized');
            }

            if ($e instanceof TooManyRequestsHttpException) {
                return $response->errorResponse('Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau!', Response::HTTP_TOO_MANY_REQUESTS, 'Unauthorized');
            }

            if ($e instanceof NotFoundHttpException) {
                return redirect('/docs/api');
            }

            // Exception ...
            return $response->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, 'Error Exception');
        });
    })->create();
