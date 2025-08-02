<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageController extends Controller
{
    public function getWorldHeritageById(
        int $id,
        GetWorldHeritageByIdUseCase $useCase
    ): JsonResponse
    {
        $dto = $useCase->handle($id);

        $viewModel = new WorldHeritageViewModel($dto);

        return response()->json([
            'status' => 'success',
            'data' => $viewModel->toArray(),
        ], 200);
    }
}