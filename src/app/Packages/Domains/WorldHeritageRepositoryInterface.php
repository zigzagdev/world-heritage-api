<?php

namespace App\Packages\Domains;

interface WorldHeritageRepositoryInterface
{
    public function insertHeritage(
        WorldHeritageEntity $heritage
    ): WorldHeritageEntity;

    public function insertHeritages(
        WorldHeritageEntityCollection $collection
    ): WorldHeritageEntityCollection;

    public function updateOneHeritage(
        WorldHeritageEntity $entity
    ): WorldHeritageEntity;

    public function updateManyHeritages(
        WorldHeritageEntityCollection $collection
    ): WorldHeritageEntityCollection;

    public function deleteOneHeritage(
        int $id
    ): void;
}