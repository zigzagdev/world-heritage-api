<?php

use Illuminate\Support\Facades\Route;
use App\Packages\Features\Controller\WorldHeritageController;

Route::prefix('v1')->group(function () {
    Route::get('/heritages/{id}', [WorldHeritageController::class, 'getWorldHeritageById']);
    Route::get('heritages/', [WorldHeritageController::class, 'getWorldHeritagesByIds']);
    Route::post('heritage', [WorldHeritageController::class, 'registerOneWorldHeritage']);
    Route::post('heritages', [WorldHeritageController::class, 'registerManyWorldHeritages']);
    Route::put('heritages/{id}', [WorldHeritageController::class, 'updateOneWorldHeritage']);
    Route::delete('heritages/{id}', [WorldHeritageController::class, 'deleteOneHeritage']);
});