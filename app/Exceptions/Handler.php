<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [
        QueryException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    public function render($request, Throwable $e): mixed
    {
        if ($this->isDatabaseConnectionException($e)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => __('global.database_connection_error'),
                ], 503);
            }
            return response()->view('shop.offline', [], 503);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->renderJsonError($request, $e);
        }

        return parent::render($request, $e);
    }

    private function renderJsonError($request, Throwable $e): mixed
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'message' => $e->getMessage() ?: __('global.server_error'),
            ], $e->getStatusCode());
        }

        \Log::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->json([
            'message' => __('global.server_error'),
        ], $status);
    }

    private function isDatabaseConnectionException(Throwable $e): bool
    {
        if ($e instanceof QueryException) {
            $code = $e->getPrevious()?->getCode();
            if (in_array((string) $code, ['08006', '08001', '08003', '08004', '08007', '57P01', '57P03', '53300'])) {
                return true;
            }
            $message = $e->getMessage();
            if (str_contains($message, 'could not translate host name') ||
                str_contains($message, 'could not connect to server') ||
                str_contains($message, 'connection refused') ||
                str_contains($message, 'timeout expired') ||
                str_contains($message, 'no route to host') ||
                str_contains($message, 'network unreachable')) {
                return true;
            }
        }

        if ($e instanceof \PDOException) {
            $message = $e->getMessage();
            if (str_contains($message, 'could not translate host name') ||
                str_contains($message, 'could not connect to server') ||
                str_contains($message, 'connection refused') ||
                str_contains($message, 'timeout expired') ||
                str_contains($message, 'no route to host') ||
                str_contains($message, 'network unreachable')) {
                return true;
            }
        }

        return false;
    }
}
