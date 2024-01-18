<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\CustomException;

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


        $this->renderable(function (CustomException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => false,
                'data' => $e->getData(),
            ], $e->getStatusCode());
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid data was given',
                'status' => false,
                "data" => $e->errors()
            ], 422);
        });

        $this->renderable(function (AuthenticationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                "status" => false,
                'data' => null
            ], 401);
        });
    }
}
