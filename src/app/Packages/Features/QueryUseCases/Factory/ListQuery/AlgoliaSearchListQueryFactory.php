<?php

namespace App\Packages\Features\QueryUseCases\Factory\ListQuery;

use App\Enums\StudyRegion;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use InvalidArgumentException;

class AlgoliaSearchListQueryFactory
{
    private const ALLOWED_CRITERIA = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'];

    public static function build(
        ?string $keyword,
        ?string $countryName,
        ?string $countryIso3,
        ?string $region,
        ?string $category,
        ?int $yearFrom,
        ?int $yearTo,
        ?array $criteria,
        ?bool $isEndangered,
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

        if ($criteria !== null) {
            foreach ($criteria as $value) {
                if (!in_array($value, self::ALLOWED_CRITERIA, true)) {
                    $printable = is_scalar($value) ? (string) $value : gettype($value);
                    throw new InvalidArgumentException(
                        "Invalid criteria value: {$printable}"
                    );
                }
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
            criteria: $criteria,
            isEndangered: $isEndangered,
            currentPage: $currentPage,
            perPage: $perPage,
        );
    }
}