<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class SearchWorldHeritagesTest extends TestCase
{
    private WorldHeritageSearchPort $searchPort;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('algolia.algolia_app_id', 'dummy');
        config()->set('algolia.algolia_search_api_key', 'dummy');
        config()->set('algolia.algolia_index', 'dummy');

        $this->searchPort = Mockery::mock(WorldHeritageSearchPort::class);
        $this->app->bind(WorldHeritageSearchPort::class, function () {
            return $this->searchPort;
        });

        $this->refresh();
        (new DatabaseSeeder())->run();
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
        WorldHeritage::truncate();
        Country::truncate();
        DB::table('site_state_parties')->truncate();
        Image::truncate();
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function test_feature_api_ok_with_ids(): void
    {
        $this->searchPort
            ->shouldReceive('search')
            ->once()
            ->andReturn(new HeritageSearchResult(
                ids: [661,663],
                total: 2,
                currentPage: 3,
                perPage: 10,
                lastPage: 1
            ));

        $result = $this->getJson('/api/v1/heritages/search?search_query=japan&current_page=3&per_page=10');

        $result->assertOk();

        $this->assertCount(2, $result->json('data.items'));
        $this->assertSame(661, $result->json('data.items.0.id'));
        $this->assertSame('Himeji-jo', $result->json('data.items.0.name'));
        $this->assertSame(663, $result->json('data.items.1.id'));
        $this->assertSame('Shirakami-Sanchi', $result->json('data.items.1.name'));
    }

    public function test_feature_api_with_typo_return_correct_value(): void
    {
        $this->searchPort
            ->shouldReceive('search')
            ->once()
            ->andReturn(new HeritageSearchResult(
                ids: [661,663],
                total: 2,
                currentPage: 1,
                perPage: 30,
                lastPage: 1
            ));

        $result = $this->getJson('/api/v1/heritages/search?search_query=Jpan&current_page=1&per_page=30');

        $result->assertOk();

        $this->assertCount(2, $result->json('data.items'));
    }

    public function test_feature_api_with_no_result(): void
    {
        $this->searchPort
            ->shouldReceive('search')
            ->once()
            ->andReturn(new HeritageSearchResult(
                ids: [],
                total: 0,
                currentPage: 1,
                perPage: 30,
                lastPage: 0
            ));

        $result = $this->getJson('/api/v1/heritages/search?search_query=Ecuador&current_page=1&per_page=30');

        $result->assertOk();
    }
}