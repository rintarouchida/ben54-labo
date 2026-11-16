<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
        // API 全体に1IPあたり毎分60回のレート制限をかける(リミッター 'api' は AppServiceProvider で定義)
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Accept ヘッダーがなくても、/api 配下のエラーは HTML ではなく JSON で返す
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        // 404: 存在しない ID(findOrFail の ModelNotFoundException も NotFoundHttpException に変換されて届く)、
        // 数値でない ID(ルートの where に一致しない)、未定義のパス。ボディは5.4節の形式
        $exceptions->render(function (NotFoundHttpException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return response()->json(['message' => '弁護士が見つかりません。'], 404);
            }

            return null;
        });

        // 429 などその他の HTTP 例外: APP_DEBUG=true でもスタックトレースを付けず、message だけ返す。
        // Retry-After などの例外が持つヘッダーはそのまま付ける
        $exceptions->render(function (HttpExceptionInterface $e, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }
            $status = $e->getStatusCode();
            $message = $e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$status] ?? 'Error');

            return response()->json(['message' => $message], $status, $e->getHeaders());
        });

        // 500: 予期しない例外はスタックトレースを返さず、決まったメッセージだけ返す。
        // 例外の記録(storage/logs)は render と別に report で行われる。
        // 422(ValidationException)は Laravel 標準の { message, errors } のまま返す
        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->is('api/*') || $e instanceof ValidationException) {
                return null;
            }

            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        });
    })->create();
