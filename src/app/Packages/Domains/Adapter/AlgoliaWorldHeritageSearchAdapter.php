<?php

namespace App\Packages\Domains\Adapter;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;

class AlgoliaWorldHeritageSearchAdapter implements WorldHeritageSearchPort
{
    public function __construct(
        private SearchClient $client,
        private string $indexName,
    ) {}

    public function search(AlgoliaSearchListQuery $query, int $currentPage, int $perPage): HeritageSearchResult
    {
        $firstPage = max(0, $currentPage - 1);

        $filters = [];
        if ($query->country) {
            $filters[] = 'country:"' . addslashes($query->country) . '"';
        }
        if ($query->region) {
            $filters[] = 'region:"' . addslashes($query->region) . '"';
        }
        if ($query->category) {
            $filters[] = 'category:"' . addslashes($query->category) . '"';
        }
        if ($query->yearFrom !== null) {
            $filters[] = 'year_inscribed >= ' . (int) $query->yearFrom;
        }
        if ($query->yearTo !== null) {
            $filters[] = 'year_inscribed <= ' . (int) $query->yearTo;
        }

        $response = $this->client->searchSingleIndex(
            $this->indexName,
            array_filter(
                [
                    'query' => $query->keyword ?? '',
                    'page' => $firstPage,
                    'hitsPerPage' => $perPage,
                    'filters' => $filters ? implode(' AND ', $filters) : null,
                ],
                fn($v) => $v !== null,
            ),
        );

        $hits = $response['hits'] ?? [];

        $ids = array_values(array_filter(array_map(function (array $h) {
            if (isset($h['id']))
                return (int) $h['id'];
            if (isset($h['objectID']))
                return (int) $h['objectID'];
            return null;
        }, $hits)));

        return new HeritageSearchResult(ids: $ids, total: (int) ($response['nbHits'] ?? 0));
    }
}
