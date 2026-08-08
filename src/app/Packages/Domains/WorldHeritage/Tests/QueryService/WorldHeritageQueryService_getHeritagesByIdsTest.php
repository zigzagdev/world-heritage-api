<?php

namespace App\Packages\Domains\WorldHeritage\Tests\QueryService;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Models\WorldHeritageDescription;
use App\Packages\Domains\WorldHeritage\WorldHeritageQueryService;
use App\Packages\Domains\WorldHeritage\Ports\WorldHeritageSearchPort;
use App\Packages\Domains\WorldHeritage\Ports\Dto\HeritageSearchResult;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorldHeritageQueryService_getHeritagesByIdsTest extends TestCase
{
    private WorldHeritageQueryService $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $seeder = new DatabaseSeeder();
        $seeder->run();

        $this->app->bind(WorldHeritageSearchPort::class, static function () {
            return new class implements WorldHeritageSearchPort {
                public function search($query, int $currentPage, int $perPage): HeritageSearchResult {
                    return new HeritageSearchResult(ids: [], total: 0, currentPage: 1, perPage: $perPage, lastPage: 0);
                }
            };
        });

        $this->queryService = app(WorldHeritageQueryService::class);
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
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            Image::truncate();
            WorldHeritageDescription::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_returns_world_heritage_dto_collection(): void
    {
        $ids = DB::table('world_heritage_sites')->orderBy('id')->limit(2)->pluck('id')->all();

        $result = $this->queryService->getHeritagesByIds($ids);

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }

    public function test_preserves_order_of_given_ids(): void
    {
        $ids = DB::table('world_heritage_sites')->orderBy('id')->limit(3)->pluck('id')->all();
        $this->assertCount(3, $ids, 'Seeder must insert at least 3 world heritages.');

        $requested = [$ids[2], $ids[0], $ids[1]];

        $result = $this->queryService->getHeritagesByIds($requested);

        $resultIds = array_map(fn ($dto) => $dto->getId(), $result->getHeritages());
        $this->assertSame($requested, $resultIds);
    }

    public function test_returns_empty_collection_when_no_ids_given(): void
    {
        $result = $this->queryService->getHeritagesByIds([]);

        $this->assertSame([], $result->getHeritages());
    }

    public function test_skips_missing_ids_without_failing(): void
    {
        $existingId = (int) DB::table('world_heritage_sites')->orderBy('id')->value('id');
        $this->assertNotNull($existingId, 'Seeder must insert at least 1 world heritage.');

        $result = $this->queryService->getHeritagesByIds([$existingId, 999_999_999]);

        $resultIds = array_map(fn ($dto) => $dto->getId(), $result->getHeritages());
        $this->assertSame([$existingId], $resultIds);
    }
}
