<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

class AlgoliaSearchListQuery
{
    public function __construct(
        public readonly ?string $keyword,
        public readonly ?string $countryName,
        public readonly ?string $countryIso3,
        public readonly ?string $region,
        public readonly ?string $category,
        public readonly ?int $yearFrom,
        public readonly ?int $yearTo,
        public readonly int $currentPage,
        public readonly int $perPage,
    ) {}

    public function effectiveQuery(): string
    {
        return $this->countryIso3 ? '' : ($this->keyword ?? '');
    }

    public function isIsoSearch(): bool
    {
        return $this->countryIso3 !== null;
    }
}