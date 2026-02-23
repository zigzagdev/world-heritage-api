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

    public function search(
        AlgoliaSearchListQuery $query,
        int $currentPage,
        int $perPage
    ): HeritageSearchResult {

        /**
         * Algolia uses 0-based pagination.
         * Convert the application's 1-based page number to 0-based.
         */
        $firstPage = max(0, $currentPage - 1);

        /**
         * Build an array of Algolia filter expressions.
         * These will later be joined using "AND".
         */
        $filters = [];

        /**
         * If an ISO3 country code is provided:
         *
         * Do NOT perform full-text search (query = '').
         * Filter strictly by state_party_codes.
         *
         * This represents a different search strategy from
         * the regular country name search.
         */
        if ($query->countryIso3) {
            $filters[] = 'state_party_codes:' . $query->countryIso3;
            $queryString = '';
        } else {
            /**
             * Default behaviour:
             * Perform standard full-text search using keyword.
             */
            $queryString = $query->keyword ?? '';

            /**
             * If a country name is provided,
             * apply a strict match filter on the "country" field.
             */
            if ($query->countryName) {
                $filters[] = 'country:"' . addslashes($query->countryName) . '"';
            }
        }

        /**
         * Apply region filter (exact match).
         */
        if ($query->region) {
            $filters[] = 'region:"' . addslashes($query->region) . '"';
        }

        /**
         * Apply category filter (exact match).
         */
        if ($query->category) {
            $filters[] = 'category:"' . addslashes($query->category) . '"';
        }

        /**
         * Apply numeric range filters for inscription year.
         */
        if ($query->yearFrom !== null) {
            $filters[] = 'year_inscribed >= ' . (int) $query->yearFrom;
        }

        if ($query->yearTo !== null) {
            $filters[] = 'year_inscribed <= ' . (int) $query->yearTo;
        }

        /**
         * Execute the Algolia search request.
         *
         * - query: full-text search string
         * - page: zero-based page index
         * - hitsPerPage: number of results per page
         * - filters: combined filter conditions
         */
        $response = $this->client->searchSingleIndex(
            $this->indexName,
            array_filter(
                [
                    'query' => $queryString,
                    'page' => $firstPage,
                    'hitsPerPage' => $perPage,
                    'filters' => $filters ? implode(' AND ', $filters) : null,
                ],
                fn ($v) => $v !== null,
            ),
        );

        /**
         * Extract search hits from the response.
         */
        $hits = $response['hits'] ?? [];

        /**
         * Extract entity IDs from each hit.
         * Depending on index configuration, either "id" or "objectID" is used.
         */
        $ids = array_values(array_filter(array_map(function (array $h) {
            return isset($h['id'])
                ? (int) $h['id']
                : (isset($h['objectID']) ? (int) $h['objectID'] : null);
        }, $hits)));

        /**
         * Return the search result DTO containing:
         * - Ordered list of IDs (for DB re-fetching)
         */
        return new HeritageSearchResult(
            ids: $ids,
            total: (int) ($response['nbHits'] ?? 0)
        );
    }
}