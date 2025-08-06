<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Packages\Domains\WorldHeritageEntity;
use App\Common\Pagination\PaginationDto;


interface WorldHeritageQueryServiceInterface
{
    public function getHeritageById(
        int $id
    ): WorldHeritageEntity;

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto;
}