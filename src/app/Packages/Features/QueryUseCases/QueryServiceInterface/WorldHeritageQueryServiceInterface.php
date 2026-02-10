<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;


interface WorldHeritageQueryServiceInterface
{
    public function getAllHeritages(
        int $currentPage,
        int $perPage
    ): WorldHeritageDtoCollection;

    public function getHeritageById(
        int $id
    ): WorldHeritageDto;

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto;

    public function searchHeritages(
        ?string $keyword,
        ?string $country,
        ?string $region,
        ?string $category,
        ?int $yearInscribedFrom,
        ?int $yearInscribedTo,
        int $currentPage,
        int $perPage
    ): PaginationDto;
}