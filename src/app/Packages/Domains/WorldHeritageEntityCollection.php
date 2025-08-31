<?php

namespace App\Packages\Domains;

class WorldHeritageEntityCollection
{
    public function __construct(
        private array $heritages = []
    ) {}

    public function add(WorldHeritageEntity $heritage): static
    {
        $this->heritages[] = $heritage;

        return $this;
    }

    public function getAllHeritages(): array
    {
        return $this->heritages;
    }

    public function getCurrentIndex(int $index): int
    {
        return $this->heritages[$index];
    }
}
