<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Domains\WorldHeritageEntityCollection;

class WorldHeritageListQueryCollectionFactory
{
    public static function build(
        array $request
    ): WorldHeritageEntityCollection {
        $listQuery = array_map(
            fn(array $item) => WorldHeritageListQueryFactory::build($item),
            $request
        );

        return new WorldHeritageEntityCollection($listQuery);
    }
}