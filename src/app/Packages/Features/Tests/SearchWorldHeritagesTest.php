<?php

namespace App\Packages\Features\Tests;

use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritagesUseCase;
use App\Models\WorldHeritage;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use Mockery;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;

class SearchWorldHeritagesTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $this->app->bind(WorldHeritageSearchPort::class, function () {
            return new class implements WorldHeritageSearchPort {
                public function search($query, int $currentPage, int $perPage): HeritageSearchResult
                {
                    return new HeritageSearchResult(ids: [661, 663], total: 2);
                }
            };
        });

        $this->getWorldHeritagesUseCase = app(GetWorldHeritagesUseCase::class);

        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            WorldHeritage::truncate();
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_feature_api_ok_with_ids(): void
    {
        $mock = Mockery::mock(WorldHeritageSearchPort::class);
        $mock->shouldReceive('search')
            ->andReturn(new HeritageSearchResult(ids: [661,663], total: 2));

        $this->app->instance(WorldHeritageSearchPort::class, $mock);

        $result = $this->getJson('/api/v1/heritages/search?search_query=Japan&current_page=1&per_page=30');

        $result->assertStatus(200);
        $result->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'official_name',
                            'name',
                            'country',
                            'country_name_jp',
                            'region',
                            'category',
                            'year_inscribed',
                            'latitude',
                            'longitude',
                            'is_endangered',
                            'heritage_name_jp',
                            'state_party',
                            'criteria',
                            'area_hectares',
                            'buffer_zone_hectares',
                            'short_description',
                            'thumbnail',
                            'state_party_codes',
                            'state_parties_meta',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ]);
    }

    public function test_feature_api_return_check_value(): void
    {
        $result = $this->getJson('/api/v1/heritages/search?search_query=Japan&current_page=1&per_page=30');

        $result->assertStatus(200);
        $resultData = $result->json('data');

        $this->assertCount(2, $resultData);
        $this->assertEquals(661, $resultData['items'][0]['id']);
        $this->assertEquals('Himeji-jo', $resultData['items'][0]['name']);
        $this->assertEquals(663, $resultData['items'][1]['id']);
        $this->assertEquals('Shirakami-Sanchi', $resultData['items'][1]['name']);
    }

    public function test_feature_api_with_typo_return_correct_value(): void
    {
        $result = $this->getJson('/api/v1/heritages/search?search_query=Jpan&current_page=1&per_page=30');

        $result->assertStatus(200);
        $resultData = $result->json('data');

        $this->assertCount(2, $resultData);
        $this->assertEquals(661, $resultData['items'][0]['id']);
        $this->assertEquals('Himeji-jo', $resultData['items'][0]['name']);
        $this->assertEquals(663, $resultData['items'][1]['id']);
        $this->assertEquals('Shirakami-Sanchi', $resultData['items'][1]['name']);
    }

    public function test_feature_api_with_no_result(): void
    {
        $mock = Mockery::mock(WorldHeritageSearchPort::class);

        $mock->shouldReceive('search')
            ->with(
                Mockery::on(function ($arg) {
                    return $arg instanceof AlgoliaSearchListQuery
                        && $arg->keyword === 'Ecuador'
                        && $arg->currentPage === 1
                        && $arg->perPage === 30
                        && $arg->countryName === 'Ecuador'
                        && $arg->countryIso3 === 'ECU'
                        && $arg->region === null
                        && $arg->category === null
                        && $arg->yearFrom === null
                        && $arg->yearTo === null;
                }),
                1,
                30
            )
            ->andReturn(new HeritageSearchResult(ids: [], total: 0));

        $this->app->instance(WorldHeritageSearchPort::class, $mock);
        $result = $this->getJson('/api/v1/heritages/search?search_query=Ecuador&current_page=1&per_page=30');

        $result->assertStatus(200);
        $this->assertCount(0, $result->getOriginalContent()['data']['items']);
    }
}