<?php

use Illuminate\Support\Facades\Route;
use App\Packages\Features\Controller\WorldHeritageController;

Route::prefix('v1')->group(function () {
    Route::get('/heritages/{id}', [WorldHeritageController::class, 'getWorldHeritageById']);
});