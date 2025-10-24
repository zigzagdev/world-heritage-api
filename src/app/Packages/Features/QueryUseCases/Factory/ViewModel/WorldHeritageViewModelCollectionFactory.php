<?php

namespace App\Packages\Features\QueryUseCases\Factory\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModelCollection;

class WorldHeritageViewModelCollectionFactory
{
    public static function build(WorldHeritageDtoCollection $collection): WorldHeritageViewModelCollection
    {
        $items = array_map(
            fn($dto) => WorldHeritageSummaryViewModelFactory::build($dto),
            $collection->getHeritages()

        );

        return new WorldHeritageViewModelCollection($items);
    }
}