<?php

namespace App\Packages\Features\QueryUseCases\Factory\ListQuery;

use App\Enums\StudyRegion;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use InvalidArgumentException;

class AlgoliaSearchListQueryFactory
{
    public static function build(
        ?string $keyword,
        ?string $countryName,
        ?string $countryIso3,
        ?string $region,
        ?string $category,
        ?int $yearFrom,
        ?int $yearTo,
        int $currentPage,
        int $perPage,
    ): AlgoliaSearchListQuery {

        $studyRegion = null;

        if ($region !== null) {
            $studyRegion = StudyRegion::tryFrom($region);

            if ($studyRegion === null) {
                throw new InvalidArgumentException(
                    "Invalid region value: {$region}"
                );
            }
        }

        return new AlgoliaSearchListQuery(
            keyword: $keyword,
            countryName: $countryName,
            countryIso3: $countryIso3,
            region: $studyRegion,
            category: $category,
            yearFrom: $yearFrom,
            yearTo: $yearTo,
            currentPage: $currentPage,
            perPage: $perPage,
        );
    }
}