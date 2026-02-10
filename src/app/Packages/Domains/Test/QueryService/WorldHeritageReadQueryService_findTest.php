<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\Image;
use App\Packages\Domains\WorldHeritageReadQueryService;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class WorldHeritageReadQueryService_findTest extends TestCase
{
    private WorldHeritageReadQueryService $readQueryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $seeder = new DatabaseSeeder();
        $seeder->run();

        $this->readQueryService = app(WorldHeritageReadQueryService::class);
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_preserves_order_of_given_ids(): void
    {
        $ids = DB::table('world_heritage_sites')
            ->orderBy('id')
            ->limit(3)
            ->pluck('id')
            ->all();

        $this->assertCount(3, $ids, 'Seeder must insert at least 3 world heritages.');

        $requested = [$ids[2], $ids[0], $ids[1]];
        $rows = $this->readQueryService->findByIdsPreserveOrder($requested);

        $this->assertSame($requested, $rows->pluck('id')->all());
    }

    public function test_skips_missing_ids_without_failing(): void
    {
        $existingId = (int) DB::table('world_heritage_sites')->orderBy('id')->value('id');

        $this->assertNotNull($existingId, 'Seeder must insert at least 1 world heritage.');

        $requested = [$existingId, 999999999];
        $rows = $this->readQueryService->findByIdsPreserveOrder($requested);

        $this->assertSame([$existingId], $rows->pluck('id')->all());
    }

    public function test_returns_duplicates_if_input_contains_duplicates(): void
    {
        $existingId = DB::table('world_heritage_sites')->orderBy('id')->value('id');
        $this->assertNotNull($existingId, 'Seeder must insert at least 1 world heritage.');

        $requested = [$existingId, $existingId];
        $rows = $this->readQueryService->findByIdsPreserveOrder($requested);

        $this->assertSame([$existingId, $existingId], $rows->pluck('id')->all());
    }
}