<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Features\QueryUseCases\ListQuery\UpdateWorldHeritageListQueryCollection;

class UpdateWorldHeritageListQueryCollectionFactory
{
    public static function build(
        array $request
    ): UpdateWorldHeritageListQueryCollection {
        $listQuery = array_map(
            fn(array $item) => UpdateWorldHeritageListQueryFactory::build($item),
            $request
        );

        return new UpdateWorldHeritageListQueryCollection(...$listQuery);
    }
}