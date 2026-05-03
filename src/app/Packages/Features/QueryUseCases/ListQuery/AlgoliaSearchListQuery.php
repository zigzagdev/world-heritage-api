<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

use App\Enums\StudyRegion;

class AlgoliaSearchListQuery
{
    public function __construct(
        public readonly ?string $keyword,
        public readonly ?string $countryName,
        public readonly ?string $countryIso3,
        public readonly ?StudyRegion $region,
        public readonly ?string $category,
        public readonly ?int $yearFrom,
        public readonly ?int $yearTo,
        public readonly ?array $criteria,
        public readonly ?bool $isEndangered,
        public readonly int $currentPage,
        public readonly int $perPage,
    ) {}
}