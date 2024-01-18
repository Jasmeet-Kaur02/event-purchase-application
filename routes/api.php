<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('events', [EventController::class, 'create']);
    Route::put('events/{eventId}', [EventController::class, 'update']);
    Route::delete('events/{eventId}', [EventController::class, 'delete']);
    Route::get('events', [EventController::class, 'get']);
    Route::post('users/{userId}/events/{eventId}', [EventController::class, 'purchaseEvent']);

    Route::post('users/{userId}/charge-wallet', [PaymentController::class, 'chargeWallet']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout']);

Route::post('users/{userId}/registration-payment', [PaymentController::class, 'registrationPayment']);