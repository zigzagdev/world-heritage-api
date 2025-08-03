<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageEntityCollection;

interface WorldHeritageQueryServiceInterface
{
    public function getHeritageById(
        int $id
    ): WorldHeritageEntity;

    public function getHeritagesByIds(
        array $ids
    ): WorldHeritageEntityCollection;
}