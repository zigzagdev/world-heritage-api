<?php

namespace App\Packages\Features\QueryUseCases\Factory\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageSummaryViewModelFactory
{
    public static function build(WorldHeritageDto $dto): WorldHeritageViewModel
    {
        return new WorldHeritageViewModel($dto);
    }
}