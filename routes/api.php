<?php

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Login Route
    |--------------------------------------------------------------------------
    */
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('/users', UserController::class);
        // change user password
        Route::put('/users/{user}/password', [UserController::class, 'changePassword']);
        Route::put('/users/{user}/disabled', [UserController::class, 'disabledUser']);


        // building
        Route::apiResource('/buildings', BuildingController::class);
        // floor
        Route::apiResource('/floors', FloorController::class);
        // Logout route
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    });
});
