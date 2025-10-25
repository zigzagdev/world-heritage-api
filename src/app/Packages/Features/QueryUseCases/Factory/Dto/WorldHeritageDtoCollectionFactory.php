<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;

class WorldHeritageDtoCollectionFactory
{

    public static function build(array $dtos): WorldHeritageDtoCollection
    {
        $collection = new WorldHeritageDtoCollection();

        foreach ($dtos as $row) {
            $dto = WorldHeritageSummaryFactory::build($row);
            $collection->add($dto);
        }

        return $collection;
    }
}
