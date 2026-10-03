<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Enums\StudyRegion;
use App\Packages\Domains\WorldHeritage\Adapter\AlgoliaWorldHeritageSearchAdapter;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use Mockery;
use PHPUnit\Framework\TestCase;

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
        $expectedParams = [
            'query' => '',
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

    public function test_search_by_country_name_without_iso3_does_not_filter_on_state_party(): void
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
            'filters' => '(country:"Ecuador" OR country_name_jp:"Ecuador")',
            'attributesToRetrieve' => ['objectID', 'id'],
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->once()
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [],
                'nbHits' => 0,
                'nbPages' => 0,
                'page' => 0,
                'hitsPerPage' => 30,
            ]);

        $result = $adapter->search($q, currentPage: 1, perPage: 30);

        $this->assertSame([], $result->ids);
    }
}
