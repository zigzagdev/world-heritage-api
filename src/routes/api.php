<?php

use App\Packages\Features\Controller\UserController;
use App\Packages\Features\Controller\WorldHeritageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/heritages', [WorldHeritageController::class, 'getWorldHeritages']);
    Route::get('/heritages/search', [WorldHeritageController::class, 'searchWorldHeritages']);
    Route::get('heritages/region-count', [WorldHeritageController::class, 'getWorldHeritagesCountByRegion']);
    Route::get('/heritages/{id}', [WorldHeritageController::class, 'getWorldHeritageById']);

    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::patch('/users/{id}', [UserController::class, 'updateUser']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::post('/user/create', [UserController::class, 'createUser']);
});