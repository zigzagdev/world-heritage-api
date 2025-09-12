<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;

class UpdateWorldHeritageListQueryCollection
{
    private array $listQuery;

    public function __construct(
        WorldHeritageListQuery ...$listQuery
    ) {
        $this->listQuery = $listQuery;
    }

    public function toArray(): array
    {
        return array_map(
            fn(WorldHeritageListQuery $query) => $query->toArray(),
            $this->listQuery
        );
    }

    public function getItems(): array
    {
        return $this->listQuery;
    }
}
