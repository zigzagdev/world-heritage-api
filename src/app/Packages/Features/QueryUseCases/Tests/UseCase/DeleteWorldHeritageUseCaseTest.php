<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\UseCase\DeleteWorldHeritageUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DeleteWorldHeritageUseCaseTest extends TestCase
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
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface::class);

        $mock
            ->shouldReceive('deleteOneHeritage')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturn();

        return $mock;
    }

    public function test_use_case(): void
    {
        $useCase = new DeleteWorldHeritageUseCase(
            $this->mockRepository()
        );
        $useCase->handle(1418);

        $this->assertTrue(true);
    }

    public function test_use_case_ng(): void
    {
        $this->expectException(RuntimeException::class);

        $mock = Mockery::mock(WorldHeritageRepositoryInterface::class);
        $mock
            ->shouldReceive('deleteOneHeritage')
            ->with(Mockery::type('int'))
            ->once()
            ->andThrow(new RuntimeException());

        $useCase = new DeleteWorldHeritageUseCase(
            $mock
        );
        $useCase->handle(9999);
    }
}