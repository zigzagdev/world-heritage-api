<?php

namespace App\Packages\Domains\Ports\Dto;

class HeritageSearchResult
{
    public function __construct(
        public readonly array $ids,
        public readonly int $total,
    ) {}
}
