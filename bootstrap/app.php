<?php

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PurchaseAlreadyCompletedException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\SalesOrderAlreadyCompletedException;
use App\Exceptions\SalesOrderCompletionException;

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
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
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
            NotFoundHttpException $exception,
            Request $request
        ) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
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
    })
    ->create();
