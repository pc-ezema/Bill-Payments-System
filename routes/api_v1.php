<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response([
        'message' => 'You are now on Bill Payment System API endpoints'
    ], 200);
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::apiResource('users', UserController::class);
    Route::put('users', [UserController::class, 'update']);
    Route::apiResource('transactions', TransactionController::class);
    Route::put('transactions', [TransactionController::class, 'update']);
});

