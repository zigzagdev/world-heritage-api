<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Common\Pagination\PaginationDto;

class GetWorldHeritageByIdsUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService
    ) {}

    public function handle(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto
    {
        return $this->worldHeritageQueryService
            ->getHeritagesByIds(
                $ids,
                $currentPage,
                $perPage
            );
    }
}