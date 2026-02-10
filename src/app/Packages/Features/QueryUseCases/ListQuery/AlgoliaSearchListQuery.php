<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

class AlgoliaSearchListQuery
{
    public function __construct(
        public readonly ?string $keyword,
        public readonly ?string $country,
        public readonly ?string $region,
        public readonly ?string $category,
        public readonly ?int $yearFrom,
        public readonly ?int $yearTo,
        public readonly ?int $currentPage,
        public readonly ?int $perPage,
    ) {}

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getYearFrom(): ?int
    {
        return $this->yearFrom;
    }

    public function getYearTo(): ?int
    {
        return $this->yearTo;
    }

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    public function getPerPage(): ?int
    {
        return $this->perPage;
    }
}