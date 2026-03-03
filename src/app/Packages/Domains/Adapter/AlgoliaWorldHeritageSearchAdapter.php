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
         */
        $firstPage = max(0, $currentPage - 1);

        /**
         * Build Algolia filters (joined by AND later).
         */
        $filters = [];

        /**
         * Country filtering strategy:
         * - If ISO3 is explicitly provided, use strict ISO3 filtering and disable full-text search.
         * - Otherwise, attempt to treat the provided countryName as one of:
         *   - English country name (e.g. "Japan" / "japan")
         *   - Japanese name (e.g. "日本")
         *   - ISO3 (e.g. "JPN")
         *
         * In that case we build an OR-filter across relevant fields.
         */
        $queryString = $query->keyword ?? '';

        if ($this->hasValue($query->countryIso3)) {
            // Requirement: ISO3 -> query='' and filter by state_party_codes:<ISO3>
            $filters[] = 'state_party_codes:' . $this->escapeToken($query->countryIso3);
            $queryString = '';
        } else {
            if ($this->hasValue($query->countryName)) {
                $filters[] = $this->buildCountryOrFilter($query->countryName);

                /**
                 * IMPORTANT:
                 * Do not blank out query here.
                 * If the input was not a real country (e.g. "japan" typo / random word),
                 * we still want full-text search to work rather than returning "top hits".
                 */
            }
        }

        /**
         * Region filter (exact match).
         */
        if ($this->hasValue($query->region)) {
            $filters[] = 'region:"' . $this->escapeForQuotedString($query->region) . '"';
        }

        /**
         * Category filter (exact match).
         */
        if ($this->hasValue($query->category)) {
            $filters[] = 'category:"' . $this->escapeForQuotedString($query->category) . '"';
        }

        /**
         * Numeric range filters for inscription year.
         */
        if ($query->yearFrom !== null) {
            $filters[] = 'year_inscribed >= ' . (int) $query->yearFrom;
        }

        if ($query->yearTo !== null) {
            $filters[] = 'year_inscribed <= ' . (int) $query->yearTo;
        }

        /**
         * Guardrail:
         * Never execute Algolia with query='' AND no filters,
         * otherwise you will get "top results" unrelated to the user input.
         */
        $hasAnyFilter = !empty($filters);
        $hasQueryText = $this->hasValue($queryString);

        if (!$hasAnyFilter && !$hasQueryText) {
            // Prefer returning empty result rather than misleading "top hits".
            return new HeritageSearchResult(
                ids: [],
                total: 0,
                currentPage: $currentPage,
                perPage: $perPage,
                lastPage: 0,
            );
        }

        $response = $this->client->searchSingleIndex(
            $this->indexName,
            array_filter(
                [
                    'query' => $queryString,
                    'page' => $firstPage,
                    'hitsPerPage' => $perPage,
                    'filters' => $hasAnyFilter ? implode(' AND ', $filters) : null,
                ],
                fn ($v) => $v !== null,
            ),
        );

        $hits = $response['hits'] ?? [];

        $ids = array_values(array_filter(array_map(function (array $h) {
            return isset($h['id'])
                ? (int) $h['id']
                : (isset($h['objectID']) ? (int) $h['objectID'] : null);
        }, $hits)));

        $nbHits = (int) ($response['nbHits'] ?? 0);
        $nbPages = (int) ($response['nbPages'] ?? 0);
        $algoliaPage = (int) ($response['page'] ?? $firstPage);
        $hitsPerPage = (int) ($response['hitsPerPage'] ?? $perPage);

        return new HeritageSearchResult(
            ids: $ids,
            total: $nbHits,
            currentPage: $algoliaPage + 1,
            perPage: $hitsPerPage,
            lastPage: $nbPages,
        );
    }

    /**
     * Build an OR-filter that can match different country input shapes:
     * - English: country / state_party (exact match)
     * - Japanese: country_name_jp (exact match)
     * - ISO3-like: state_party_codes:<ISO3>
     *
     * Example output:
     * (country:"Japan" OR state_party:"Japan" OR country_name_jp:"日本" OR state_party_codes:JPN)
     */
    private function buildCountryOrFilter(string $raw): string
    {
        $term = trim($raw);
        $quoted = $this->escapeForQuotedString($term);

        $orParts = [
            'country:"' . $quoted . '"',
            'state_party:"' . $quoted . '"',
            'country_name_jp:"' . $quoted . '"',
        ];

        // If the term looks like ISO3 (3 letters), also try state_party_codes
        $maybeIso3 = $this->normaliseIso3Candidate($term);
        if ($maybeIso3 !== null) {
            $orParts[] = 'state_party_codes:' . $this->escapeToken($maybeIso3);
        }

        return '(' . implode(' OR ', $orParts) . ')';
    }

    private function normaliseIso3Candidate(string $term): ?string
    {
        $t = strtoupper(trim($term));

        // Accept only A-Z length 3 as ISO3 candidate.
        if (preg_match('/^[A-Z]{3}$/', $t) === 1) {
            return $t;
        }

        return null;
    }

    private function hasValue(?string $v): bool
    {
        return $v !== null && trim($v) !== '';
    }

    /**
     * For quoted string filters: country:"...".
     */
    private function escapeForQuotedString(string $s): string
    {
        // Minimal escaping for Algolia filter quoted strings.
        // Escape backslash and double-quote.
        $s = str_replace('\\', '\\\\', $s);
        return str_replace('"', '\\"', $s);
    }

    /**
     * For token filters: state_party_codes:JPN
     */
    private function escapeToken(string $s): string
    {
        // Conservative: remove spaces, keep as-is otherwise.
        return preg_replace('/\s+/', '', $s) ?? $s;
    }
}