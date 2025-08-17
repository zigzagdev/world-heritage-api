<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModelCollection;

class WorldHeritageViewModelCollectionFactory
{
    public static function build(WorldHeritageDtoCollection $collection): WorldHeritageViewModelCollection
    {
        $data = array_map(
            fn($dto) => new WorldHeritageViewModel($dto),
            $collection->getHeritages()
        );

        return new WorldHeritageViewModelCollection($data);
    }
}