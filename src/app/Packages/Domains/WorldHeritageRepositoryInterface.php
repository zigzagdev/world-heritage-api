<?php

namespace App\Packages\Domains;

interface WorldHeritageRepositoryInterface
{
    public function insertHeritage(
        WorldHeritageEntity $heritage
    ): WorldHeritageEntity;
}