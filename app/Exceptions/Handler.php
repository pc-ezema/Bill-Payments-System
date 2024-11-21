<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use BadMethodCallException;
use ErrorException;
use Throwable;

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

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' =>  str_contains($e->getMessage(), 'The route') ? 'Endpoint not found. If error persists, contact '.config('app.name').' customer care.' : (str_contains($e->getMessage(), 'No query results') ? str_replace(']', '', last(explode('\\', $e->getMessage()))) . ' not found.' : $e->getMessage())
                ], 404); // 404 - Not Found
            }
        });
        $this->renderable(function (ServiceUnavailableHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' => 'Server Error. If error persists, contact '.config('app.name').' customer care.'
                ], 503); // 503 - Service Unavailable
            }
        });
        $this->renderable(function (BadRequestHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' => 'Invalid request'
                ], 400); // 400 - Bad Request
            }
        });
        $this->renderable(function (ErrorException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' => 'Failed to get service'
                ], 500);  // 500 - Internal Server Error
            }
        });
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' => 'The method is not supported for this route.'
                ], 405);  // 405 - Method Not Allowed
            }
        });
        $this->renderable(function (BadMethodCallException $e, Request $request) {
            if ($request->is('api/*')) {
                return response([
                    'message' => 'Invalid request. If error persists, contact '.config('app.name').' customer care.'
                ], 400);  // 400 - Bad Request
            }
        });
    }
}
