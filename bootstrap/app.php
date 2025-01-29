<?php

use App\Enums\ResponseMessages;
use App\Http\Middleware\VerifyCookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend([
            VerifyCookie::class
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->json([
                'message' => ResponseMessages::TOO_MANY_REQUESTS,
                'exception' => class_basename($e),
            ], Response::HTTP_FORBIDDEN);
        });
    })->create();
