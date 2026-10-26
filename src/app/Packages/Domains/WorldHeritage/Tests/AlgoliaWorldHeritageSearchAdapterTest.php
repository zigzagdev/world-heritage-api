<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Enums\StudyRegion;
use App\Packages\Domains\WorldHeritage\Adapter\AlgoliaWorldHeritageSearchAdapter;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class AlgoliaWorldHeritageSearchAdapterTest extends TestCase
{
    private SearchClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(SearchClient::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_builds_algolia_params_with_filters_and_paging(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $q = new AlgoliaSearchListQuery(
            keyword: 'galapagos',
            countryName: 'Ecuador',
            countryIso3: 'ECU',
            region: StudyRegion::SOUTH_AMERICA,
            category: 'Natural',
            yearFrom: 1978,
            yearTo: 1980,
            criteria: [],
            isEndangered: null,
            currentPage: 2,
            perPage: 30,
        );

        // Arrange: Algolia expects 0-based page indexing, so currentPage=2 becomes page=1.
        // The keyword must stay intact even though a country filter is also present.
        $expectedParams = [
            'query' => 'galapagos',
            'page' => 1,
            'hitsPerPage' => 30,
            'filters' =>
                'state_party_codes:ECU AND study_region:"South America" AND category:"Natural" ' .
                'AND year_inscribed >= 1978 AND year_inscribed <= 1980',
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [
                    ['id' => 10],
                    ['objectID' => '11'],
                ],
                'nbHits' => 2,
                'nbPages' => 1,
                'page' => 1,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 2, perPage: 30);

        $this->assertSame([10, 11], $result->ids);
        $this->assertSame(2, $result->total);
        $this->assertSame(2, $result->currentPage);
        $this->assertSame(1, $result->lastPage);
    }

    public function test_search_by_country_name_without_iso3_uses_optional_filter_not_filter(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $q = new AlgoliaSearchListQuery(
            keyword: null,
            countryName: 'Ecuador',
            countryIso3: null,
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 30,
        );

        $expectedParams = [
            'query' => '',
            'page' => 0,
            'hitsPerPage' => 30,
            'optionalFilters' => ['(country:"Ecuador" OR country_name_jp:"Ecuador")'],
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [['id' => 1]],
                'nbHits' => 1,
                'nbPages' => 1,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        $this->assertSame([1], $result->ids);
    }

    public function test_search_by_lowercase_country_name_boosts_ranking_via_optional_filter(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $q = new AlgoliaSearchListQuery(
            keyword: null,
            countryName: 'japan',
            countryIso3: null,
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 30,
        );

        $expectedParams = [
            'query' => '',
            'page' => 0,
            'hitsPerPage' => 30,
            'optionalFilters' => ['(country:"japan" OR country_name_jp:"japan")'],
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [['id' => 42]],
                'nbHits' => 1,
                'nbPages' => 1,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        $this->assertSame([42], $result->ids);
    }

    public function test_search_by_country_name_that_does_not_resolve_still_runs_full_text_search(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        // "zzz" does not match any real country (and isn't a plausible ISO3
        // either), but should not force a 0-result short-circuit the way a
        // mandatory $filters entry would.
        $q = new AlgoliaSearchListQuery(
            keyword: null,
            countryName: 'zzz',
            countryIso3: null,
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 30,
        );

        $expectedParams = [
            'query' => '',
            'page' => 0,
            'hitsPerPage' => 30,
            'optionalFilters' => ['(country:"zzz" OR country_name_jp:"zzz" OR state_party_codes:ZZZ)'],
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [['id' => 7], ['id' => 8]],
                'nbHits' => 2,
                'nbPages' => 1,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        // The point of optionalFilters: a non-matching country term does not
        // force an empty result via the adapter's own guardrail.
        $this->assertSame([7, 8], $result->ids);
    }

    public function test_search_logs_and_skips_a_hit_with_a_non_numeric_id_instead_of_silently_dropping_it(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $q = new AlgoliaSearchListQuery(
            keyword: 'temple',
            countryName: null,
            countryIso3: null,
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 30,
        );

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->andReturn([
                'hits' => [
                    ['id' => 10],
                    ['id' => 'not-a-number', 'objectID' => 'broken-1'],
                ],
                'nbHits' => 2,
                'nbPages' => 1,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Algolia search hit has a non-numeric id', [
                'id' => 'not-a-number',
                'objectID' => 'broken-1',
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        // The valid hit survives; the broken one is logged and skipped rather
        // than silently coerced to 0 and dropped via array_filter.
        $this->assertSame([10], $result->ids);
    }

    public function test_search_combines_keyword_with_country_iso3_filter_instead_of_dropping_keyword(): void
    {
        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $q = new AlgoliaSearchListQuery(
            keyword: 'temple',
            countryName: null,
            countryIso3: 'JPN',
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 30,
        );

        $expectedParams = [
            'query' => 'temple',
            'page' => 0,
            'hitsPerPage' => 30,
            'filters' => 'state_party_codes:JPN',
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [['id' => 913], ['id' => 1277]],
                'nbHits' => 2,
                'nbPages' => 1,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        $this->assertSame([913, 1277], $result->ids);
    }
}
