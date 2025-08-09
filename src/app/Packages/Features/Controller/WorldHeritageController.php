<?php

namespace App\Packages\Features\Controller;

use App\Common\Pagination\PaginationViewModel;
use App\Http\Controllers\Controller;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
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

    public function getWorldHeritagesByIds(
        GetWorldHeritageByIdsUseCase $useCase,
        array $ids,
        int $currentPage = 1,
        int $perPage = 10,
    ): JsonResponse
    {
        $dto = $useCase->handle($ids, $currentPage, $perPage);

        $viewModel = new PaginationViewModel($dto);

        return response()->json(
            $viewModel->toArray(),
            200
        );
    }
}