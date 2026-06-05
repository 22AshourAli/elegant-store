<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        QueryException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): mixed
    {
        if ($request->isMethod('GET') && !$request->expectsJson()) {
            if ($this->isDatabaseConnectionException($e)) {
                return response()->view('shop.offline', [], 503);
            }
        }

        return parent::render($request, $e);
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
