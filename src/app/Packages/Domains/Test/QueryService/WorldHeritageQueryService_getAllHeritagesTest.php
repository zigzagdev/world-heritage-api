<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;

class WorldHeritageQueryService_getAllHeritagesTest extends TestCase
{

    private $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_fetch_data_check_type(): void
    {
        $result = $this->queryService->getAllHeritages();

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }

    public function test_fetch_data_check_value(): void
    {
        $result = $this->queryService->getAllHeritages();

        $this->assertCount(9, $result->toArray());
        $this->assertContains(1133, array_column($result->toArray(), 'id'));
        $this->assertContains(1442, array_column($result->toArray(), 'id'));
    }
}