<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use Tests\TestCase;
use Mockery;

use App\Common\Pagination\PaginationDto;
use App\Enums\StudyRegion;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use App\Packages\Features\QueryUseCases\UseCase\SearchWorldHeritagesWithAlgoliaUseCase;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Domains\Infra\CountryResolver;
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
        $heritages = array_map(static function (int $id) {
            return new WorldHeritageDto(
                id: $id,
                officialName: 'test1234',
                name: 'Fuji Mountain',
                country: null,
                countryNameJp: null,
                region: 'JPN',
                category: 'Cultural',
                yearInscribed: 2000,
                latitude: null,
                longitude: null,
                isEndangered: false,
                heritageNameJp: 'テスト1234',
                stateParty: null,
                criteria: [],
                areaHectares: null,
                bufferZoneHectares: null,
                shortDescription: '',
                images: null,
                imageUrl: null,
                unescoSiteUrl: null,
                statePartyCodes: [],
                statePartiesMeta: [],
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

    public function test_search_heritages_resolves_country_name_and_calls_query_service(): void
    {
        $expectedDto = $this->makePaginationDto([661, 662], self::CURRENT_PAGE, self::PER_PAGE, 2);

        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $resolver = Mockery::mock(CountryResolver::class);
        $resolver->shouldReceive('resolveIso3')
            ->with('test country')
            ->once()
            ->andReturn('FRA');

        $queryService
            ->shouldReceive('searchHeritages')
            ->with(Mockery::on(static function (AlgoliaSearchListQuery $query) {
                return $query->keyword === 'test keyword'
                    && $query->countryName === 'test country'
                    && $query->countryIso3 === 'FRA'
                    && $query->region === StudyRegion::ASIA
                    && $query->category === 'test category'
                    && $query->yearFrom === 2000
                    && $query->yearTo === 2020
                    && $query->currentPage === self::CURRENT_PAGE
                    && $query->perPage === self::PER_PAGE;
            }))
            ->once()
            ->andReturn($expectedDto);

        $useCase = new SearchWorldHeritagesWithAlgoliaUseCase($queryService, $resolver);

        $result = $useCase->handle(
            'test keyword',
            'test country',
            null,
            'Asia', // ← 有効なEnumの値
            'test category',
            2000,
            2020,
            null,
            null,
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertSame($expectedDto, $result);

        $array = $result->toArray();
        $this->assertSame(self::CURRENT_PAGE, $array['pagination']['current_page'] ?? null);
        $this->assertSame(self::PER_PAGE, $array['pagination']['per_page'] ?? null);
        $this->assertSame(2, $array['pagination']['total'] ?? null);
    }

    public function test_search_nullable_params_calls_query_service_with_nulls(): void
    {
        $expectedDto = $this->makePaginationDto([], self::CURRENT_PAGE, self::PER_PAGE, 0);
        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);
        $resolver = Mockery::mock(CountryResolver::class);
        $resolver->shouldNotReceive('resolveIso3');

        $queryService
            ->shouldReceive('searchHeritages')
            ->with(Mockery::on(static function (AlgoliaSearchListQuery $query) {
                return $query->keyword === null
                    && $query->countryName === null
                    && $query->countryIso3 === null
                    && $query->region === null
                    && $query->category === null
                    && $query->yearFrom === null
                    && $query->yearTo === null
                    && $query->currentPage === self::CURRENT_PAGE
                    && $query->perPage === self::PER_PAGE;
            }))
            ->once()
            ->andReturn($expectedDto);

        $useCase = new SearchWorldHeritagesWithAlgoliaUseCase($queryService, $resolver);

        $result = $useCase->handle(
            null, null, null, null, null, null, null,
            null,
            null,
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertSame($expectedDto, $result);
    }
}