<?php

namespace App\Packages\Features\QueryUseCases\Dto;

class RegionCountDto
{
    public function __construct(
        public readonly string $region,
        public readonly int $count
    ) {}

    public function toArray(): array
    {
        return [
            'region' => $this->region,
            'count' => $this->count,
        ];
    }
}
