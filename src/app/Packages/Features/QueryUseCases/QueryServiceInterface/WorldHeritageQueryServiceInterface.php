<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Common\Pagination\PaginationDto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

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

    public function searchHeritages(
        AlgoliaSearchListQuery $query
    ): PaginationDto;

    public function getEachRegionsHeritagesCount(): array;
}