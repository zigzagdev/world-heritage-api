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

        $result = $this->getJson('/api/v1/heritages/search?search_query=Japan');

        $result->assertStatus(200);
        $result->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'official_name',
                        'name',
                        'country',
                        'region',
                        'category',
                        'year_inscribed',
                        'latitude',
                        'longitude',
                        'is_endangered',
                        'name_jp',
                        'state_party',
                        'criteria',
                        'area_hectares',
                        'buffer_zone_hectares',
                        'short_description',
                    ],
                ],
            ]);
    }

    public function test_feature_api_return_check_value(): void
    {
        $result = $this->getJson('/api/v1/heritages/search?search_query=Japan');

        $result->assertStatus(200);
        $resultData = $result->json('data');

        $this->assertCount(2, $resultData);
        $this->assertEquals(661, $resultData[0]['id']);
        $this->assertEquals('Himeji-jo', $resultData[0]['name']);
        $this->assertEquals(663, $resultData[1]['id']);
        $this->assertEquals('Shirakami-Sanchi', $resultData[1]['name']);
    }
}