<?php

use App\Exceptions\CustomMessageException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render AuthenticationException
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication Failed',
                    'error' => $e->getMessage(),
                ], Response::HTTP_UNAUTHORIZED);
            }
        });

        // Render CustomMessageException
        $exceptions->render(function (CustomMessageException $e, Request $request) {
            if ($request->is('api/*')) {
                return $e->render();
            }
        });

        // Render ParseError
        $exceptions->render(function (\ParseError $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Syntax error occurred',
                    'error' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });

        // Render Throwable
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error',
                    'error' => $e->getMessage(),
                    'details' => $e->getTrace(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });

        // Default JSON rendering for other exceptions
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();
