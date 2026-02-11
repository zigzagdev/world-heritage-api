<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Common\Pagination\PaginationDto;

class SearchWorldHeritagesWithAlgoliaUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $queryService
    ) {}

    public function handle(
        ?string $keyword,
        ?string $country,
        ?string $region,
        ?string $category,
        ?int $yearInscribedFrom,
        ?int $yearInscribedTo,
        int $currentPage,
        int $perPage
    ): PaginationDto {
        return $this->queryService->searchHeritages(
            $keyword,
            $country,
            $region,
            $category,
            $yearInscribedFrom,
            $yearInscribedTo,
            $currentPage,
            $perPage
        );
    }
}
