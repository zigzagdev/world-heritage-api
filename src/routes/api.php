<?php

use Illuminate\Support\Facades\Route;
use App\Packages\Features\Controller\WorldHeritageController;

Route::prefix('v1')->group(function (): void {
    Route::get('/heritages', [WorldHeritageController::class, 'getWorldHeritages']);
    Route::get('/heritages/search', [WorldHeritageController::class, 'searchWorldHeritages']);
    Route::get('heritages/region-count', [WorldHeritageController::class, 'getWorldHeritagesCountByRegion']);
    Route::get('/heritages/{id}', [WorldHeritageController::class, 'getWorldHeritageById']);
});