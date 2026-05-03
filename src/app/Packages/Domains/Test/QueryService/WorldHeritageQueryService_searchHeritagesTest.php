<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Enums\StudyRegion;
use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageReadQueryServiceInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use App\Models\WorldHeritageDescription;

final class WorldHeritageQueryService_searchHeritagesTest extends TestCase
{
    private WorldHeritageQueryService $queryService;
    private AlgoliaSearchListQuery $listQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        (new DatabaseSeeder())->run();

        $this->listQuery = new AlgoliaSearchListQuery(
            keyword: 'galapagos',
            countryName: null,
            countryIso3: null,
            region: StudyRegion::ASIA,
            category: null,
            yearFrom: null,
            yearTo: null,
            criteria: [],
            isEndangered: null,
            currentPage: 1,
            perPage: 10,
        );

        $fakeSearchPort = new class implements WorldHeritageSearchPort {
            public function search($query, int $currentPage, int $perPage): HeritageSearchResult
            {
                return new HeritageSearchResult(
                    ids: [],
                    total: 0,
                    currentPage: $currentPage,
                    perPage: $perPage,
                    lastPage: 0
                );
            }
        };

        $this->app->instance(WorldHeritageSearchPort::class, $fakeSearchPort);

        $fakeReadService = new class implements WorldHeritageReadQueryServiceInterface {
            public function findByIdsPreserveOrder(array $ids): Collection
            {
                return collect();
            }
        };

        $this->app->instance(WorldHeritageReadQueryServiceInterface::class, $fakeReadService);
        $this->queryService = app(WorldHeritageQueryService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
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

    public function test_searchHeritages_orchestrates_ports_and_returns_pagination_dto(): void
    {
        $ids = DB::table('world_heritage_sites')->orderBy('id')->limit(2)->pluck('id')->all();
        $this->assertCount(2, $ids, 'Seeder must insert at least 2 world heritages.');

        $hitIds = [$ids[1], $ids[0]];
        $total = 2;

        $searchPort = Mockery::mock(WorldHeritageSearchPort::class);
        $searchPort
            ->shouldReceive('search')
            ->once()
            ->withArgs(function ($query, $currentPage, $perPage) {
                $this->assertInstanceOf(AlgoliaSearchListQuery::class, $query);
                $this->assertSame(1, $currentPage);
                $this->assertSame(10, $perPage);
                return true;
            })
            ->andReturn(new HeritageSearchResult(
                ids: $hitIds,
                total: $total,
                currentPage: 1,
                perPage: 10,
                lastPage: 1
            ));

        $this->app->instance(WorldHeritageSearchPort::class, $searchPort);

        $modelsById = WorldHeritage::query()
            ->whereIn('id', $hitIds)
            ->get()
            ->keyBy('id');

        $ordered = collect($hitIds)
            ->map(static fn ($id) => $modelsById->get($id))
            ->filter();

        $readQueryService = Mockery::mock(WorldHeritageReadQueryServiceInterface::class);
        $readQueryService
            ->shouldReceive('findByIdsPreserveOrder')
            ->once()
            ->with($hitIds)
            ->andReturn($ordered);

        $this->app->instance(WorldHeritageReadQueryServiceInterface::class, $readQueryService);
        $queryService = app(WorldHeritageQueryService::class);

        $dto = $queryService->searchHeritages($this->listQuery);

        $this->assertSame(1, $dto->getCurrentPage());
        $this->assertSame(10, $dto->getPerPage());
        $this->assertSame($total, $dto->getTotal());
        $this->assertSame(1, $dto->getLastPage());

        $heritageIds = collect($dto->getCollection()->getHeritages())
            ->map(static fn ($h) => $h->getId())
            ->all();

        $this->assertCount(2, $heritageIds);
        $this->assertSame($hitIds, $heritageIds);
    }
}