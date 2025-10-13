<?php

namespace App\Packages\Domains\Test\Repository;

use App\Models\Image;
use App\Packages\Domains\WorldHeritageRepository;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;
use Database\Seeders\DatabaseSeeder;
use RuntimeException;

class WorldHeritageRepository_deleteTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->repository = app(WorldHeritageRepository::class);
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
             Country::truncate();
             DB::table('site_state_parties')->truncate();
             Image::truncate();
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_delete_ok(): void
    {
        $this->repository->deleteOneHeritage(1418);

        $this->assertDatabaseHas('world_heritage_sites', [
            'id' => 1418,
            'deleted_at' => now(),
        ]);

        $this->assertDatabaseHas('images', [
            'world_heritage_id' => 1418,
            'deleted_at' => now(),
        ]);
    }

    public function test_delete_ng_wrong_id(): void
    {
        $this->expectException(RuntimeException::class);

        $this->repository->deleteOneHeritage(9999);
    }
}