<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\CustomException;
use Stripe_ApiConnectionError;
use Stripe_ApiError;
use Stripe_AuthenticationError;
use Stripe_Error;


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

        $this->renderable(function (Stripe_AuthenticationError | Stripe_ApiConnectionError $e) {
            return response()->json([
                'message' => "Error processing the request. Please try again",
                'status' => false,
                'data' => null,
            ], 500);
        });

        $this->renderable(function (Stripe_ApiError $e) {
            return response()->json([
                'message' => "Stripe API error. Please try again",
                'status' => false,
                'data' => null,
            ], 500);
        });

        $this->renderable(function (Stripe_Error $e) {
            return response()->json([
                'message' => "An unexpected error occurred. Please try again",
                'status' => false,
                'data' => null,
            ], 500);
        });
    }
}
