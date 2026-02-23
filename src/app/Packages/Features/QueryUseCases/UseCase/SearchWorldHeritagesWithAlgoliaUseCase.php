<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\Infra\CountryResolver;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Common\Pagination\PaginationDto;

class SearchWorldHeritagesWithAlgoliaUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $queryService,
        private readonly CountryResolver $resolver
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

        $isoCode = null;

        if (isset($country)) {
            $isoCode = $this->resolver->resolveIso3($country);
        }

        return $this->queryService->searchHeritages(
            $keyword,
            $isoCode ?? $country,
            $region,
            $category,
            $yearInscribedFrom,
            $yearInscribedTo,
            $currentPage,
            $perPage
        );
    }
}
