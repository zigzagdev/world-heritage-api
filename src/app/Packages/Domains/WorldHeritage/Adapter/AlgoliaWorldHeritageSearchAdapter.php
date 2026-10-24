<?php

namespace App\Packages\Domains\WorldHeritage\Adapter;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Packages\Domains\WorldHeritage\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\WorldHeritage\Ports\WorldHeritageSearchPort;
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
         * Optional filters: unlike $filters, these never exclude a record —
         * they only boost the ranking of records that match. Used when a
         * country has not been confirmed via ISO3, so an input that fails to
         * resolve to a real country (e.g. "Jap") still falls back to plain
         * full-text search instead of returning 0 results.
         */
        $optionalFilters = [];

        /**
         * Country filtering strategy:
         * - If ISO3 is explicitly provided, filter by state_party_codes:<ISO3>.
         *   This is a confirmed country, so it narrows results like any other filter.
         * - Otherwise, attempt to treat the provided countryName as one of:
         *   - English country name (e.g. "Japan" / "japan")
         *   - Japanese name (e.g. "日本")
         *   - ISO3 (e.g. "JPN")
         *
         *   This is unconfirmed, so it goes into $optionalFilters: matching
         *   records rank higher, but a non-matching countryName does not
         *   reduce the full-text search results to zero.
         *
         * Either way, the country signal never blanks out the keyword: like
         * every other filter (region/category/year/criteria/is_endangered),
         * it narrows/boosts alongside the keyword search rather than
         * replacing it. A keyword combined with an explicit country param
         * (e.g. keyword="temple" & country_iso3=JPN) must narrow by both.
         */
        $queryString = $query->keyword ?? '';

        if ($this->hasValue($query->countryIso3)) {
            $filters[] = 'state_party_codes:' . $this->escapeToken($query->countryIso3);
        } elseif ($this->hasValue($query->countryName)) {
            $optionalFilters[] = $this->buildCountryOrFilter($query->countryName);
        }

        /**
         * Region filter (exact match).
         */
        if ($query->region !== null) {
            $filters[] = 'study_region:"' . $this->escapeForQuotedString($query->region->value) . '"';
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
         * Inscription criteria filter (OR within criteria).
         * Values are pre-validated by AlgoliaSearchListQueryFactory against the i–x whitelist.
         */
        if ($query->criteria !== null && $query->criteria !== []) {
            $orParts = array_map(
                static fn (string $criterion) => 'criteria:' . $criterion,
                $query->criteria,
            );
            $filters[] = '(' . implode(' OR ', $orParts) . ')';
        }

        /**
         * Endangered status filter (boolean facet).
         */
        if ($query->isEndangered !== null) {
            $filters[] = 'is_endangered:' . ($query->isEndangered ? 'true' : 'false');
        }

        /**
         * Guardrail:
         * Never execute Algolia with query='' AND no filters AND no optional filters,
         * otherwise you will get "top results" unrelated to the user input.
         */
        $hasAnyFilter = $filters !== [];
        $hasOptionalFilter = $optionalFilters !== [];
        $hasQueryText = $this->hasValue($queryString);

        if (!$hasAnyFilter && !$hasOptionalFilter && !$hasQueryText) {
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
                    'optionalFilters' => $hasOptionalFilter ? $optionalFilters : null,
                    /**
                     * Only the id is ever read from the response (see below):
                     * the full record is re-fetched from the DB via findByIdsPreserveOrder.
                     * Restrict the payload accordingly.
                     */
                    'attributesToRetrieve' => ['objectID', 'id'],
                ],
                static fn ($v) => $v !== null,
            ),
        );

        $hits = $response['hits'] ?? [];

        $ids = array_values(array_filter(array_map(static function (array $h) {
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
     * Build an OR-expression that can match different country input shapes:
     * - English: country (exact match)
     * - Japanese: country_name_jp (exact match)
     * - ISO3-like: state_party_codes:<ISO3>
     *
     * Used as an entry in $optionalFilters (not $filters): a non-match
     * does not exclude a record, it just skips the ranking boost.
     *
     * Example output:
     * (country:"Japan" OR country_name_jp:"日本" OR state_party_codes:JPN)
     */
    private function buildCountryOrFilter(string $raw): string
    {
        $term = trim($raw);
        $quoted = $this->escapeForQuotedString($term);

        $orParts = [
            'country:"' . $quoted . '"',
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