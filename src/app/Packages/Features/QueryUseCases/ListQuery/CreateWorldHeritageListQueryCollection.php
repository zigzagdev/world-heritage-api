<?php

namespace App\Packages\Features\QueryUseCases\ListQuery;

class CreateWorldHeritageListQueryCollection
{
    private array $listQuery;

    public function __construct(
        CreateWorldHeritageListQuery ...$listQuery
    ) {
        $this->listQuery = $listQuery;
    }

    public function toArray(): array
    {
        return array_map(
            fn(CreateWorldHeritageListQuery $query) => $query->toArray(),
            $this->listQuery
        );
    }
}