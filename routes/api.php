<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/** 
 * WebAdmin Controllers 
 */
use App\Http\Controllers\Api\WebAdmin\UserController;
use App\Http\Controllers\Api\WebAdmin\RoleController;
use App\Http\Controllers\Api\WebAdmin\ColaboratorController;
use App\Http\Controllers\Api\WebAdmin\FitcoinAccountController;
use App\Http\Controllers\Api\WebAdmin\FitcoinTransactionController;

/** 
 * AppMobile Controllers 
 */
use App\Http\Controllers\Api\AppMobile\AuthController;
use App\Http\Controllers\Api\AppMobile\ActivityController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// WebAdmin login (generates token)
Route::post('login', [UserController::class, 'login']);

// Ping
Route::get('ping', function () {
    return response()->json([
        'message' => 'pong',
        'status'  => 'success',
    ]);
});


/*
|--------------------------------------------------------------------------
| WebAdmin (protected by sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('webadmin')->group(function () {
    // user info
    Route::get('user', function(Request $request) {
        return $request->user();
    });

    // CRUD: roles, users, colaborators
    Route::apiResource('roles',       RoleController::class);
    Route::apiResource('users',       UserController::class);
    Route::apiResource('colaborators',ColaboratorController::class);

    // Fitcoin accounts & transactions
    Route::get( 'fitcoin/accounts',                    [FitcoinAccountController::class,     'index']);
    Route::get( 'fitcoin/accounts/{colaborator}',      [FitcoinAccountController::class,     'show']);
    Route::get( 'fitcoin/accounts/{colaborator}/txns', [FitcoinTransactionController::class, 'index']);
    Route::post('fitcoin/accounts/{colaborator}/txns', [FitcoinTransactionController::class, 'store']);

    // WebAdmin logout
    Route::post('logout', [UserController::class, 'logout']);
});


/*
|--------------------------------------------------------------------------
| AppMobile (prefix "app")
|--------------------------------------------------------------------------
*/
Route::prefix('app')->group(function () {
    // Public
    Route::post('login',  [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        // Current user
        Route::get('user',   [AuthController::class, 'user']);
        // Logout
        Route::post('logout',[AuthController::class, 'logout']);
        // Activities
        Route::get( 'activities', [ActivityController::class, 'index']);
        Route::post('activities', [ActivityController::class, 'store']);
    });
});
