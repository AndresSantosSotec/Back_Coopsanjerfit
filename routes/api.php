<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebAdmin\UserController;
use App\Http\Controllers\Api\WebAdmin\RoleController;
use App\Http\Controllers\Api\WebAdmin\ColaboratorController;  // ← añade esto

// Login público
Route::post('/login', [UserController::class, 'login']);

// Logout (requiere token válido)
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Obtener info del usuario logueado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Módulo WebAdmin
    Route::prefix('webadmin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('colaborators', ColaboratorController::class);  // ← y esto
    });
});

// Ping público
Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'status'  => 'success',
    ]);
});
