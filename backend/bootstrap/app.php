<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API-02: 存在しない ID(findOrFail の ModelNotFoundException も NotFoundHttpException に変換されて届く)と
        // 数値でない ID(ルートの where に一致しない)を、5.4節の形式の404にする
        $exceptions->render(function (NotFoundHttpException $e, Request $request): ?JsonResponse {
            if ($request->is('api/v1/lawyers/*')) {
                return response()->json(['message' => '弁護士が見つかりません。'], 404);
            }

            return null;
        });
    })->create();
