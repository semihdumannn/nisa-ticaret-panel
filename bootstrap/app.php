<?php

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (HF Spaces / Cloudflare reverse proxy)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        // ── Middleware aliases ────────────────────────────────────────────────
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        // ── Enable API rate limiting ──────────────────────────────────────────
        // Applies 'throttle:api' to all routes in the 'api' middleware group.
        $middleware->throttleApi('api');

        // ── Security headers on every API response ────────────────────────────
        $middleware->appendToGroup('api', SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── Uniform JSON error responses for all API routes ───────────────────

        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                $model = class_basename($e->getModel());
                return response()->json(['message' => "{$model} not found."], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Route not found.'], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message'     => 'Too many requests. Please slow down.',
                    'retry_after' => (int) ($e->getHeaders()['Retry-After'] ?? 60),
                ], 429);
            }
        });

        $exceptions->render(function (\App\Modules\Review\Domain\Exceptions\ReviewNotAllowedException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['error' => $e->errorCode], 422);
            }
        });

        // ── Generic HttpException (e.g. Spatie role/permission 403) ──────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->is('api/*')) {
                $status  = $e->getStatusCode();
                $message = $e->getMessage() ?: match ($status) {
                    401     => 'Unauthenticated.',
                    403     => 'This action is unauthorized.',
                    404     => 'Not found.',
                    405     => 'Method not allowed.',
                    default => 'HTTP error.',
                };
                return response()->json(['message' => $message], $status);
            }
        });

        // ── Catch-all for unhandled exceptions ────────────────────────────────
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $debug   = config('app.debug');
                $payload = ['message' => $debug ? $e->getMessage() : 'An unexpected error occurred.'];

                if ($debug) {
                    $payload['exception'] = get_class($e);
                    $payload['file']      = $e->getFile();
                    $payload['line']      = $e->getLine();
                }

                return response()->json($payload, 500);
            }
        });
    })->create();
