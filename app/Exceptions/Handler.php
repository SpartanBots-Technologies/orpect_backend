<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\QueryException;
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'statusCode' => '405',
                'error' => 'Method Not Allowed',
                'message' => 'The requested method is not supported for this route.'
            ], 405);
        }
        if ($exception instanceof RouteNotFoundException) {
            return response()->json([
                'statusCode' => '404',
                'error' => 'Route Not Found',
                'message' => 'The requested route could not be found.'
            ], 404);
        }
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'statusCode' => '404',
                'error' => 'Not Found',
                'message' => 'The requested resource could not be found.'
            ], 404);
        }
        if ($exception instanceof QueryException) {
            return response()->json([
                'statusCode' => '500',
                'error' => 'Database Error',
                'message' => 'An error occurred while executing the database query.'
            ], 500);
        }
        return parent::render($request, $exception);
    }
}
