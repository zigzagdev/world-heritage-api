<?php

namespace App\Packages\Features\Controller;

use App\Common\Pagination\PaginationViewModel;
use App\Http\Controllers\Controller;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\UpdateWorldHeritageListQueryCollectionFactory;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\UpdateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\Factory\ViewModel\WorldHeritageViewModelCollectionFactory;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldManyHeritagesUseCase;
use App\Packages\Features\QueryUseCases\UseCase\DeleteWorldHeritagesUseCase;
use App\Packages\Features\QueryUseCases\UseCase\DeleteWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritagesUseCase;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritagesUseCase;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

        return response()->json([
            'status' => 'success',
            'data' => $dto->toArray()
        ], 200);
    }

    public function getWorldHeritagesByIds(
        Request $request,
        GetWorldHeritageByIdsUseCase $useCase,
        GetWorldHeritagesUseCase $indexUseCase
    ): JsonResponse
    {
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            $dtoCollection = $indexUseCase->handle();

            return response()->json(
                $dtoCollection->toArray(),
                200
            );
        } else {

            $heritageIds = array_map(
                fn ($user_id) => intval($user_id),
                explode(',', $ids)
            );
            $currentPage = $request->get('current_page', 1);
            $perPage = $request->get('per_page', 30);

            $dto = $useCase->handle($heritageIds, $currentPage, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $dto->toArray(),
            ], 200);
        }
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

    public function registerManyWorldHeritages(
        Request $request,
        CreateWorldManyHeritagesUseCase $useCase
    ): JsonResponse {
        DB::beginTransaction();
        try {
            $listQueryObject = $useCase->handle($request->all());

            $viewModel = WorldHeritageViewModelCollectionFactory::build($listQueryObject);

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

    public function updateOneWorldHeritage(
        int $id,
        Request $request,
        UpdateWorldHeritageUseCase $useCase
    ): JsonResponse
    {
        DB::beginTransaction();
        try {
            $commandObject = UpdateWorldHeritageListQueryFactory::build($request->all());

            $updateTargetObject = $useCase->handle($commandObject);

            $viewModel = new WorldHeritageViewModel($updateTargetObject);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $viewModel->toArray(),
            ], 200);

        } catch(Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateManyHeritages(
        Request $request,
        UpdateWorldHeritagesUseCase $useCase
    ): JsonResponse
    {
        DB::beginTransaction();
        try {
            $listQueryObject = UpdateWorldHeritageListQueryCollectionFactory::build($request->all());

            $result = $useCase->handle($listQueryObject);

            $viewModel = WorldHeritageViewModelCollectionFactory::build($result);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $viewModel->toArray(),
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteOneHeritage(
        int $id,
        DeleteWorldHeritageUseCase $useCase
    ): JsonResponse {
        DB::beginTransaction();
        try {

            $useCase->handle($id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Heritage was deleted.",
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function deleteManyHeritages(
        Request $request,
        DeleteWorldHeritagesUseCase $useCase
    ): JsonResponse {
        DB::beginTransaction();
        try {

            $request->validate([
                'ids' => ['bail', 'required', 'string', 'regex:/^\s*\d+(?:\s*,\s*\d+)*\s*$/'],
            ], [
                'ids.regex' => 'IDs must be a comma-separated list of integers.',
            ]);

            $ids = $request->get('ids', []);
            $heritageIds = array_map(
                fn ($id) => intval($id),
                explode(',', $ids)
            );

            $useCase->handle($heritageIds);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Heritages were deleted.",
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}