<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

class GetWorldHeritagesUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService
    ){}

    public function handle(
        int $currentPage,
        int $perPage
    ): PaginationDto
    {
        return $this->worldHeritageQueryService->getAllHeritages(
            $currentPage,
            $perPage
        );
    }
}