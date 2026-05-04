<?php

namespace App\Packages\Domains\Test;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Enums\StudyRegion;
use App\Packages\Domains\Adapter\AlgoliaWorldHeritageSearchAdapter;
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
}
