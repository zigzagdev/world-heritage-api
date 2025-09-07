<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\UseCase\DeleteWorldHeritageUseCase;
use App\Models\WorldHeritage;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use Mockery;
use RuntimeException;

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