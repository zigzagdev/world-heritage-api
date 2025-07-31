<?php

namespace App\Packages\Features\QueryUseCases;

use App\Packages\Domains\WorldHeritageEntity;

interface WorldHeritageQueryServiceInterface
{
    public function getHeritageById(
        int $id
    ): WorldHeritageEntity;
}