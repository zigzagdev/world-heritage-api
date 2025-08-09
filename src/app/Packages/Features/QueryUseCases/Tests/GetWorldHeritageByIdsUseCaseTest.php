<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Domains\WorldHeritageQueryService;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Common\Pagination\PaginationDto;
use Mockery;

class GetWorldHeritageByIdsUseCaseTest extends TestCase
{
    private int $currentPage;
    private int $perPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currentPage = 1;
        $this->perPage = 10;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayData(): array
    {
        return [
            [
                'id' => 1,
                'unesco_id' => '660',
                'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name_jp' => '法隆寺地域の仏教建造物',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6147,
                'longitude' => 135.7355,
                'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
            ],
            [
                'id' => 2,
                'unesco_id' => '661',
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'name_jp' => '姫路城',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "A masterpiece of Japanese castle architecture in original form.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661/',
            ],
            [
                'id' => 3,
                'unesco_id' => '662',
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'name_jp' => '屋久島',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 30.3581,
                'longitude' => 130.546,
                'short_description' => "A subtropical island with ancient cedar forests and diverse ecosystems.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662/',
            ],
            [
                'id' => 4,
                'unesco_id' => '663',
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'name_jp' => '白神山地',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 40.5167,
                'longitude' => 140.05,
                'short_description' => "Pristine beech forest with minimal human impact.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663/',
            ]
        ];
    }

    private function mockQueryService(): WorldHeritageQueryServiceInterface
    {
        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $queryService
            ->shouldReceive('getHeritagesByIds')
            ->with(
                Mockery::type('array'),
                $this->currentPage,
                $this->perPage
            )
            ->andReturn($this->mockPagination());

        return $queryService;
    }

    private function mockPagination(): PaginationDto
    {
        $pagination = Mockery::mock(PaginationDto::class);

        $pagination
            ->shouldReceive('getPath')
            ->andReturn('http://example.com/api/heritages');

        $pagination
            ->shouldReceive('getCurrentPage')
            ->andReturn($this->currentPage);

        $pagination
            ->shouldReceive('getPerPage')
            ->andReturn($this->perPage);

        $pagination
            ->shouldReceive('getTotalItems')
            ->andReturn(count(self::arrayData()));

        $pagination
            ->shouldReceive('getTotalPages')
            ->andReturn(1);

        $pagination
            ->shouldReceive('getFrom')
            ->andReturn(1);

        $pagination
            ->shouldReceive('getTo')
            ->andReturn(count(self::arrayData()));

        $pagination
            ->shouldReceive('getItems')
            ->andReturn(self::arrayData());

        $pagination
            ->shouldReceive('getCollection')
            ->andReturn(self::arrayData());

        $pagination
            ->shouldReceive('toArray')
            ->andReturn([
                'path'         => 'http://example.com/api/heritages',
                'current_page' => $this->currentPage,
                'per_page'     => $this->perPage,
                'total'        => count(self::arrayData()),
                'last_page'    => 1,
                'from'         => 1,
                'to'           => count(self::arrayData()),
                'data'         => self::arrayData(),
            ]);

        return $pagination;
    }

    public function test_use_case_check_type(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $queryService = $this->mockQueryService();
        $useCase = new GetWorldHeritageByIdsUseCase($queryService);

        $result = $useCase->handle($ids, $this->currentPage, $this->perPage);

        $this->assertInstanceOf(PaginationDto::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $queryService = $this->mockQueryService();
        $useCase = new GetWorldHeritageByIdsUseCase($queryService);

        $result = $useCase->handle($ids, $this->currentPage, $this->perPage);

        $this->assertEquals('http://example.com/api/heritages', $result->getPath());
        $this->assertEquals($this->currentPage, $result->getCurrentPage());
        $this->assertEquals($this->perPage, $result->getPerPage());
        $this->assertCount(count(self::arrayData()), $result->getCollection());
    }
}