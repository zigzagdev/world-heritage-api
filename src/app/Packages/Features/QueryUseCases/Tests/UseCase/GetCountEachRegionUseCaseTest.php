<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Packages\Features\QueryUseCases\Dto\RegionCountDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\UseCase\GetCountEachRegionUseCase;
use Tests\TestCase;
use Mockery;

class GetCountEachRegionUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    private function mockQueryService(): WorldHeritageQueryServiceInterface
    {
        $mock = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $mock->shouldReceive('getEachRegionsHeritagesCount')
            ->andReturn(self::arrayData());

        return $mock;
    }

    private static function arrayData(): array
    {
        return [
            'Asia' => 10,
            'Europe' => 5,
            'Africa' => 3,
            'South America' => 2,
            'North America' => 1,
            'Oceania' => 4,
        ];
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new GetCountEachRegionUseCase(
            $this->mockQueryService()
        );

        $result = $useCase->handle();

        $this->assertInstanceOf(RegionCountDto::class, $result[0]);
    }

    public function test_use_case_check_data(): void
    {
        $useCase = new GetCountEachRegionUseCase(
            $this->mockQueryService()
        );

        $result = $useCase->handle();

        $this->assertSame('Asia', $result[0]->region);
        $this->assertSame(10, $result[0]->count);
    }
}