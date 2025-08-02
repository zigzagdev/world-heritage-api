<?php

namespace App\Packages\Domains;

use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityCollection
{
    public function __construct(
        private array $heritages = []
    ) {}

    public function add(WorldHeritageEntity $heritage): void
    {
        $this->heritages[] = $heritage;
    }

    public function getAllHeritages(): array
    {
        return $this->heritages;
    }
}