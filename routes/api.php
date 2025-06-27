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
use App\Http\Controllers\Api\WebAdmin\ActivityController as AdminActivityController;
use App\Http\Controllers\Api\WebAdmin\DashboardController;
use App\Http\Controllers\Api\WebAdmin\NotificationController;
use App\Http\Controllers\Api\WebAdmin\GeneralInfoController as AdminGeneralInfoController;

/**
 * AppMobile Controllers
 */

use App\Http\Controllers\Api\AppMobile\AuthController;
use App\Http\Controllers\Api\AppMobile\ActivityController;
use App\Http\Controllers\Api\AppMobile\CollaboratorController;
use App\Http\Controllers\Api\AppMobile\NotificationController as MobileNotificationController;
use App\Http\Controllers\Api\AppMobile\GeneralInfoController;

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
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    // CRUD: roles, users, colaborators
    Route::apiResource('roles',       RoleController::class);
    Route::apiResource('users',       UserController::class);
    Route::apiResource('colaborators', ColaboratorController::class);

    // Fitcoin accounts & transactions
    Route::get('fitcoin/accounts',                    [FitcoinAccountController::class,     'index']);
    Route::get('fitcoin/accounts/{colaborator}',      [FitcoinAccountController::class,     'show']);
    Route::get('fitcoin/accounts/{colaborator}/txns', [FitcoinTransactionController::class, 'index']);
    Route::post('fitcoin/accounts/{colaborator}/txns', [FitcoinTransactionController::class, 'store']);

    // WebAdmin logout
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('activities',                  [AdminActivityController::class, 'index']);
    Route::get('users/{user}/activities',     [AdminActivityController::class, 'byUser']);
    Route::get('stats', [DashboardController::class, 'index']);

    // Notifications
    Route::post("notifications/send", [NotificationController::class, "send"]);
    // General Information
    Route::apiResource("info", AdminGeneralInfoController::class);
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
        Route::get('user',         [AuthController::class,        'user']);
        Route::post('logout',      [AuthController::class,        'logout']);
        // Asegúrate de usar el mismo spelling que la URL
        Route::get('collaborator', [CollaboratorController::class, 'show']);
        // o si prefieres sin doble “l”:
        // Route::get('colaborator', [CollaboratorController::class, 'show']);

        Route::get('activities',   [ActivityController::class,    'index']);
        Route::post('activities',  [ActivityController::class,    'store']);
        //obntener lista de actividades del usuario logeado
        Route::get('user/activities', [ActivityController::class, 'getUserActivities']);
        Route::post('user/photo',          [AuthController::class, 'updatePhoto']);
        Route::post('user/change-password', [AuthController::class, 'changePassword']);

        Route::get('notifications', [MobileNotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [MobileNotificationController::class, 'markAsRead']);
        Route::delete('notifications/{id}', [MobileNotificationController::class, 'destroy']);
        Route::get('info', [GeneralInfoController::class, 'index']);

    });
    Route::get('info', [GeneralInfoController::class, 'index']);

});
