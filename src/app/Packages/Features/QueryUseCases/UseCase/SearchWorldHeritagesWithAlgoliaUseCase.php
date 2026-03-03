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
        ?string $countryName,
        ?string $countryIso3,
        ?string $region,
        ?string $category,
        ?int $yearInscribedFrom,
        ?int $yearInscribedTo,
        int $currentPage,
        int $perPage
    ): PaginationDto {

        $keyword = $keyword !== null ? trim($keyword) : null;
        $countryName = $countryName !== null ? trim($countryName) : null;
        $countryIso3 = $countryIso3 !== null ? trim($countryIso3) : null;

        $resolvedIso3 = null;
        $resolvedCountryName = null;

        // 1) If the client explicitly provided country_name, try to resolve it.
        if ($countryName !== null && $countryName !== '') {
            $resolvedIso3 = $this->resolver->resolveIso3($countryName);
            if ($resolvedIso3 !== null) {
                $resolvedCountryName = $countryName;
            }
        }

        // 2) If country is not provided, optionally treat keyword as a country ONLY when resolvable.
        if ($resolvedIso3 === null && ($countryIso3 === null || $countryIso3 === '') && $keyword !== null && $keyword !== '') {
            $maybeIso3 = $this->resolver->resolveIso3($keyword);
            if ($maybeIso3 !== null) {
                $resolvedIso3 = $maybeIso3;
                $resolvedCountryName = $keyword;
            }
        }

        // 3) countryIso3 param wins only if it is valid (exists in resolver dictionary).
        if ($countryIso3 !== null && $countryIso3 !== '') {
            $maybeIso3 = $this->resolver->resolveIso3($countryIso3);
            if ($maybeIso3 !== null) {
                $resolvedIso3 = $maybeIso3;
                // We do NOT force a countryName here
                if ($resolvedCountryName === null) {
                    $resolvedCountryName = null;
                }
            }
        }

        return $this->queryService->searchHeritages(
            keyword: $keyword,
            countryName: $resolvedCountryName,
            countryIso3: $resolvedIso3,
            region: $region,
            category: $category,
            yearInscribedFrom: $yearInscribedFrom,
            yearInscribedTo: $yearInscribedTo,
            currentPage: $currentPage,
            perPage: $perPage,
        );
    }
}
