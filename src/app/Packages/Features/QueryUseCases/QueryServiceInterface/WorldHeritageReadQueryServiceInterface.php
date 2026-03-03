<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use Illuminate\Support\Collection;

interface WorldHeritageReadQueryServiceInterface
{
    public function findByIdsPreserveOrder(
        array $ids
    ): Collection;
}