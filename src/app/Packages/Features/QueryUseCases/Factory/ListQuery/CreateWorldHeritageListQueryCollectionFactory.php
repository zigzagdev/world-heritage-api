<?php

namespace App\Packages\Features\QueryUseCases\Factory\ListQuery;

use App\Packages\Domains\WorldHeritageEntityCollection;

class CreateWorldHeritageListQueryCollectionFactory
{
    public static function build(
        array $request
    ): WorldHeritageEntityCollection {
        $listQuery = array_map(
            fn(array $item) => CreateWorldHeritageListQueryFactory::build($item),
            $request
        );

        return new WorldHeritageEntityCollection($listQuery);
    }
}