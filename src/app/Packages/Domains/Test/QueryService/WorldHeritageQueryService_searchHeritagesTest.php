<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Mockery;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Domains\WorldHeritageReadQueryService;
use Database\Seeders\DatabaseSeeder;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;

class WorldHeritageQueryService_searchHeritagesTest extends TestCase
{
    private WorldHeritageQueryService $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $seeder = new DatabaseSeeder();
        $seeder->run();
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
            ->withArgs(function ($query, $currentPage, $perPage) use ($hitIds) {
                $this->assertInstanceOf(AlgoliaSearchListQuery::class, $query);

                $this->assertSame(1, $currentPage);
                $this->assertSame(10, $perPage);
                return true;
            })
            ->andReturn(new HeritageSearchResult(
                ids: $hitIds,
                total: $total
            ));

        $readQueryService = Mockery::mock(WorldHeritageReadQueryService::class);

        $models = WorldHeritage::query()
            ->whereIn('id', $hitIds)
            ->get()
            ->keyBy('id');

        $ordered = new Collection();
        foreach ($hitIds as $id) {
            $ordered->push($models->get($id));
        }

        $readQueryService
            ->shouldReceive('findByIdsPreserveOrder')
            ->once()
            ->with($hitIds)
            ->andReturn($ordered);

        $this->queryService = new WorldHeritageQueryService(
            model: new WorldHeritage(),
            readQueryService: $readQueryService,
            searchPort: $searchPort,
        );

        $dto = $this->queryService->searchHeritages(
            keyword: 'Japan',
            country: null,
            region: null,
            category: null,
            yearInscribedFrom: null,
            yearInscribedTo: null,
            currentPage: 1,
            perPage: 10
        );

        $this->assertSame(1, $dto->getCurrentPage());
        $this->assertSame(10, $dto->getPerPage());
        $this->assertSame($total, $dto->getTotal());
        $this->assertSame(1, $dto->getLastPage());

        $heritageIds = collect($dto->getCollection()->getHeritages())
            ->map(fn($h) => $h->getId())
            ->all();

        $this->assertCount(2, $heritageIds);
        $this->assertSame($hitIds, $heritageIds);
    }
}