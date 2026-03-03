<?php

use Illuminate\Support\Facades\Route;
use App\Packages\Features\Controller\WorldHeritageController;

Route::prefix('v1')->group(function () {
    Route::get('/heritages', [WorldHeritageController::class, 'getWorldHeritages']);
    Route::get('/heritages/search', [WorldHeritageController::class, 'searchWorldHeritages']);
    Route::get('/heritages/{id}', [WorldHeritageController::class, 'getWorldHeritageById']);
    Route::post('heritage', [WorldHeritageController::class, 'registerOneWorldHeritage']);
    Route::post('heritages', [WorldHeritageController::class, 'registerManyWorldHeritages']);
    Route::put('heritages/{id}', [WorldHeritageController::class, 'updateOneWorldHeritage']);
    Route::put('heritages', [WorldHeritageController::class, 'updateManyHeritages']);
    Route::delete('heritages/{id}', [WorldHeritageController::class, 'deleteOneHeritage']);
    Route::delete('heritages', [WorldHeritageController::class, 'deleteManyHeritages']);
});