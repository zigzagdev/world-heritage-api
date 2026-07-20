<?php

use App\Packages\Features\Controller\AuthController;
use App\Packages\Features\Controller\UserController;
use App\Packages\Features\Controller\WorldHeritageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::controller(WorldHeritageController::class)->prefix('heritages')->group(function (): void {
        Route::get('/', 'getWorldHeritages');
        Route::get('/search', 'searchWorldHeritages');
        Route::get('/region-count', 'getWorldHeritagesCountByRegion');
        Route::get('/{id}', 'getWorldHeritageById');
    });

    Route::post('/user/create', [UserController::class, 'createUser']);

    Route::controller(AuthController::class)->prefix('user')->group(function (): void {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
        Route::get('/me', 'me')->middleware('auth:sanctum');
    });

    Route::controller(UserController::class)->prefix('users')->group(function (): void {
        Route::get('/{id}', 'getUserById');
        Route::patch('/{id}', 'updateUser');
        Route::delete('/{id}', 'deleteUser');
    });
});