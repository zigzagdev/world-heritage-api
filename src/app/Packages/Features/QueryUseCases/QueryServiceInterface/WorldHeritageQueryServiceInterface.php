<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;


interface WorldHeritageQueryServiceInterface
{
    public function getHeritageById(
        int $id
    ): WorldHeritageDto;

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto;
}