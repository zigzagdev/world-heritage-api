<?php

namespace App\Packages\Domains\Test;

use Algolia\AlgoliaSearch\Api\SearchClient;
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
        parent::tearDown();
    }

    public function test_search_builds_algolia_params_with_filters_and_paging(): void
    {
        // Arrange: Algolia expects 0-based page indexing, so currentPage=2 becomes page=1.
        // Also ensure filters are combined with AND in the correct syntax.

        $indexName = 'world_heritage';
        $adapter = new AlgoliaWorldHeritageSearchAdapter($this->client, $indexName);

        $query = new AlgoliaSearchListQuery(
            keyword: 'galapagos',
            country: 'Ecuador',
            region: 'LAC',
            category: 'Natural',
            yearFrom: 1978,
            yearTo: 1980,
            currentPage: null,
            perPage: null,
        );

        $expectedParams = [
            'query' => 'galapagos',
            'page' => 1,
            'hitsPerPage' => 30,
            'filters' =>
                'country:"Ecuador" AND region:"LAC" AND category:"Natural" ' .
                'AND year_inscribed >= 1978 AND year_inscribed <= 1980',
        ];

        $this->client
            ->shouldReceive('searchSingleIndex')
            ->with($indexName, $expectedParams)
            ->andReturn([
                'hits' => [
                    ['id' => 10],
                    ['objectID' => '11'],
                ],
                'nbHits' => 2,
            ]);

        $result = $adapter->search($query, currentPage: 2, perPage: 30);

        $this->assertSame([10, 11], $result->ids);
        $this->assertSame(2, $result->total);
    }
}
