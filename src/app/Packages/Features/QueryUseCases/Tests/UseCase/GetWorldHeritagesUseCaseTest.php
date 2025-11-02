<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Models\Image;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritagesUseCase;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;

class GetWorldHeritagesUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

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

    private function mockQueryService(): WorldHeritageQueryServiceInterface
    {
        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $queryService
            ->shouldReceive('getAllHeritages')
            ->andReturn();

        return $queryService;
    }

    public function test_use_case(): void
    {
        $useCase = new GetWorldHeritagesUseCase($this->mockQueryService());

        $result = $useCase->handle();

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }
}