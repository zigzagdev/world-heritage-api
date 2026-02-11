<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use Tests\TestCase;
use Mockery;

use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\UseCase\SearchWorldHeritagesWithAlgoliaUseCase;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;

class SearchWorldHeritagesWithAlgoliaUseCaseTest extends TestCase
{
    private const CURRENT_PAGE = 1;
    private const PER_PAGE = 30;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makePaginationDto(array $heritageIds, int $currentPage, int $perPage, int $total): PaginationDto
    {
        $heritages = array_map(function (int $id) {
            return new WorldHeritageDto(
                id: $id,
                officialName: 'test1234',
                name: 'Fuji Mountain',
                country: null,
                region: 'JPN',
                category: 'Cultural',
                yearInscribed: 2000,
                latitude: null,
                longitude: null,
                isEndangered: false,
                nameJp: null,
                stateParty: null,
                criteria: [],
                areaHectares: null,
                bufferZoneHectares: null,
                shortDescription: '',
                images: null,
                unescoSiteUrl: null,
                statePartyCodes: [],
                statePartiesMeta: [],
                imageUrl: null,
            );
        }, $heritageIds);

        $collection = new WorldHeritageDtoCollection();

        foreach ($heritages as $heritage) {
            $collection->add($heritage);
        }

        return new PaginationDto(
            collection: $collection,
            pagination: [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / max(1, $perPage)),
            ]
        );
    }

    public function test_search_heritages_use_case_calls_query_service_and_returns_pagination_dto(): void
    {
        $expectedDto = $this->makePaginationDto([661, 662], self::CURRENT_PAGE, self::PER_PAGE, 2);

        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);
        $queryService
            ->shouldReceive('searchHeritages')
            ->with(
                'test keyword',
                'test country',
                'test region',
                'test category',
                2000,
                2020,
                self::CURRENT_PAGE,
                self::PER_PAGE
            )
            ->andReturn($expectedDto);

        $useCase = new SearchWorldHeritagesWithAlgoliaUseCase($queryService);

        $result = $useCase->handle(
            'test keyword',
            'test country',
            'test region',
            'test category',
            2000,
            2020,
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertSame($expectedDto, $result);

        $array = $result->toArray();
        $this->assertSame(self::CURRENT_PAGE, $array['pagination']['current_page'] ?? null);
        $this->assertSame(self::PER_PAGE, $array['pagination']['per_page'] ?? null);
        $this->assertSame(2, $ar['pagination']['total'] ?? null);
    }

    public function test_search_nullable_params_use_case_calls_query_service_with_nulls(): void
    {
        $expectedDto = $this->makePaginationDto([], self::CURRENT_PAGE, self::PER_PAGE, 0);

        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);
        $queryService
            ->shouldReceive('searchHeritages')
            ->with(
                null,
                null,
                null,
                null,
                null,
                null,
                self::CURRENT_PAGE,
                self::PER_PAGE
            )
            ->andReturn($expectedDto);

        $useCase = new SearchWorldHeritagesWithAlgoliaUseCase($queryService);

        $result = $useCase->handle(
            null,
            null,
            null,
            null,
            null,
            null,
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertSame($expectedDto, $result);
    }
}
