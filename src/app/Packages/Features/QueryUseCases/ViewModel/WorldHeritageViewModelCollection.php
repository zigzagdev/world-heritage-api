<?php

namespace App\Packages\Features\QueryUseCases\ViewModel;

class WorldHeritageViewModelCollection
{
    private array $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (!($item instanceof WorldHeritageViewModel)) { continue; }

$this->items[] = $item;
        }
    }

    public function toArray(): array
    {
        return array_map(
            static fn(WorldHeritageViewModel $item) => $item->toArray(),
            $this->items
        );
    }

    public function add(WorldHeritageViewModel $item): void
    {
        $this->items[] = $item;
    }

    public function count(): int
    {
        return count($this->items);
    }
}