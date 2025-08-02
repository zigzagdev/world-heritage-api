<?php

namespace App\Packages\Features\QueryUseCases\QueryServiceInterface;

use App\Packages\Domains\WorldHeritageEntity;

interface WorldHeritageQueryServiceInterface
{
    public function getHeritageById(
        int $id
    ): WorldHeritageEntity;
}