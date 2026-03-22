<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Common\Pagination\PaginationDto;
use App\Enums\StudyRegion;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;


interface WorldHeritageQueryServiceInterface
{
    public function getAllHeritages(
        int $currentPage,
        int $perPage,
        string $order
    ): PaginationDto;

    public function getHeritageById(
        int $id
    ): WorldHeritageDto;

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto;

    public function searchHeritages(
        AlgoliaSearchListQuery $query
    ): PaginationDto;

    public function getEachRegionsHeritagesCount(): array;
}