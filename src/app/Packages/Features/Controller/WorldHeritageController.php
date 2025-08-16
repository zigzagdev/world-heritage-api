<?php

namespace App\Packages\Features\Controller;

use App\Common\Pagination\PaginationViewModel;
use App\Http\Controllers\Controller;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Exception;

class WorldHeritageController extends Controller
{
    public function getWorldHeritageById(
        Request $request,
        GetWorldHeritageByIdUseCase $useCase
    ): JsonResponse
    {
        $id = $request->route('id');
        if (empty($id)) {
            throw new InvalidArgumentException('Id parameter is required.');
        }

        $dto = $useCase->handle($id);

        $viewModel = new WorldHeritageViewModel($dto);

        return response()->json([
            'status' => 'success',
            'data' => $viewModel->toArray(),
        ], 200);
    }

    public function getWorldHeritagesByIds(
        Request $request,
        GetWorldHeritageByIdsUseCase $useCase,
    ): JsonResponse
    {
        $ids = $request->get('ids', []);

        $heritageIds = array_map(
            fn ($user_id) => intval($user_id),
            explode(',', $ids)
        );
        $currentPage = $request->get('current_page', 1);
        $perPage = $request->get('per_page', 30);

        $dto = $useCase->handle($heritageIds, $currentPage, $perPage);

        $viewModel = new PaginationViewModel($dto);

        return response()->json(
            $viewModel->toArray(),
            200
        );
    }

    public function registerOneWorldHeritage(
        Request $request,
        CreateWorldHeritageUseCase $useCase
    ): JsonResponse
    {
        DB::beginTransaction();
        try {
            $listQueryObject = $useCase->handle($request->all());

            $viewModel = new WorldHeritageViewModel($listQueryObject);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $viewModel->toArray(),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}