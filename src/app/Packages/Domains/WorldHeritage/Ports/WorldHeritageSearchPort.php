<?php

namespace App\Packages\Domains\WorldHeritage\Ports;

use App\Packages\Domains\WorldHeritage\Ports\Dto\HeritageSearchResult;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;

interface WorldHeritageSearchPort
{
    public function search(AlgoliaSearchListQuery $query, int $currentPage, int $perPage): HeritageSearchResult;
}
