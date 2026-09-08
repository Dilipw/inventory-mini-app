<?php

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PurchaseAlreadyCompletedException;
use App\Exceptions\SalesOrderAlreadyCompletedException;
use App\Exceptions\SalesOrderCompletionException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (
            InsufficientStockException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        });

        $exceptions->render(function (
            PurchaseAlreadyCompletedException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        });

        $exceptions->render(function (
            SalesOrderAlreadyCompletedException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        });

        $exceptions->render(function (
            SalesOrderCompletionException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        });

        $exceptions->render(function (
            QueryException $exception,
            Request $request
        ) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Resource cannot be deleted because it is associated with existing records.',
                ], 409);
            }

            throw $exception;
        });

        $exceptions->render(function (
            NotFoundHttpException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        });
    })
    ->create();