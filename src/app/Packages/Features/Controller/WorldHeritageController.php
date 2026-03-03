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
use App\Packages\Features\QueryUseCases\UseCase\SearchWorldHeritagesWithAlgoliaUseCase;
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
    public function getWorldHeritages(
        Request $request,
        GetWorldHeritagesUseCase $useCase
    ): JsonResponse
    {
        $currentPage = $request->get('current_page', 1);
        $perPage = $request->get('per_page', 30);

        $dto = $useCase->handle($currentPage, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $dto->toArray(),
        ], 200);

    }

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
        $worldHeritageViewModel = new WorldHeritageViewModel($dto);

        return response()->json([
            'status' => 'success',
            'data' => $worldHeritageViewModel->toArray(),
        ], 200);
    }

    public function searchWorldHeritages(
        Request $request,
        SearchWorldHeritagesWithAlgoliaUseCase $useCase
    ): JsonResponse
    {
        $currentPage = (int) $request->query('current_page', 1);
        $perPage = (int) $request->query('per_page', 30);
        $keyword = $request->query('search_query');
        if ($keyword === null || trim((string) $keyword) === '') {
            $keyword = $request->query('keyword');
        }

        $dto = $useCase->handle(
            $keyword,
            $request->query('country_name'),
            $request->query('country_iso3'),
            $request->query('region'),
            $request->query('category'),
            $request->query('year_inscribed_from'),
            $request->query('year_inscribed_to'),
            $currentPage,
            $perPage
        );

        return response()->json([
            'status' => 'success',
            'data' => $dto->toArray(),
        ], 200);
    }
}