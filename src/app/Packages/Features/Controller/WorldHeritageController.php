<?php

namespace App\Packages\Features\Controller;

use App\Http\Controllers\Controller;
use App\Packages\Features\QueryUseCases\UseCase\GetCountEachRegionUseCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use App\Packages\Features\QueryUseCases\UseCase\SearchWorldHeritagesWithAlgoliaUseCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritagesUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $order = $request->get('order', 'asc');

        $dto = $useCase->handle($currentPage, $perPage, $order);

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

        $criteriaParam = $request->query('criteria');
        $criteria = match (true) {
            is_array($criteriaParam) => $criteriaParam,
            is_string($criteriaParam) && $criteriaParam !== '' => explode(',', $criteriaParam),
            default => null,
        };

        $isEndangered = $request->has('is_endangered')
            ? $request->boolean('is_endangered')
            : null;

        $dto = $useCase->handle(
            $keyword,
            $request->query('country_name'),
            $request->query('country_iso3'),
            $request->query('region'),
            $request->query('category'),
            $request->query('year_inscribed_from'),
            $request->query('year_inscribed_to'),
            $criteria,
            $isEndangered,
            $currentPage,
            $perPage,
        );

        return response()->json([
            'status' => 'success',
            'data' => $dto->toArray(),
        ], 200);
    }

    public function getWorldHeritagesCountByRegion(
        Request $request,
        GetCountEachRegionUseCase $useCase
    ): JsonResponse
    {
        $dto = $useCase->handle();

        return response()->json([
            'status' => 'success',
            'data' => array_map(static fn ($item) => $item->toArray(), $dto),
        ], 200);
    }
}