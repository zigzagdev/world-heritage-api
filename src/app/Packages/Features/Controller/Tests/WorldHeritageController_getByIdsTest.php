<?php

namespace App\Packages\Features\Controller\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;
use App\Common\Pagination\PaginationViewModel;
use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageController_getByIdsTest extends TestCase
{
    private $controller;
    private $currentPage;
    private $perPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new WorldHeritageController();
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
    private static function dtoItems(): array
    {
        return array_map(
            fn(array $r) => new WorldHeritageDto(
                id: $r['id'],
                unescoId: $r['unesco_id'],
                officialName: $r['official_name'],
                name: $r['name'],
                country: $r['country'],
                region: $r['region'],
                category: $r['category'],
                yearInscribed: $r['year_inscribed'],
                latitude: $r['latitude'],
                longitude: $r['longitude'],
                isEndangered: $r['is_endangered'],
                nameJp: $r['name_jp'],
                stateParty: $r['state_party'],
                criteria: $r['criteria'],
                areaHectares: $r['area_hectares'],
                bufferZoneHectares: $r['buffer_zone_hectares'],
                shortDescription: $r['short_description'],
                imageUrl: $r['image_url'],
                unescoSiteUrl: $r['unesco_site_url'],
            ),
            self::arrayData()
        );
    }


    private function mockUseCase(): GetWorldHeritageByIdsUseCase
    {
        $mock = Mockery::mock(GetWorldHeritageByIdsUseCase::class);

        $mock->shouldReceive('handle')
            ->with(
                Mockery::type('array'),
                Mockery::type('int'),
                Mockery::type('int')
            )
            ->andReturn($this->mockPaginationDto());

        return $mock;
    }

    private function mockPaginationDto(): PaginationDto
    {
        $pagination = Mockery::mock(PaginationDto::class)->makePartial();

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
            ->andReturn(self::dtoItems());

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

    public function test_controller_return_type(): void
    {
        $ids = array_column(self::arrayData(), 'id');

        $result = $this->controller->getWorldHeritagesByIds(
            $this->mockUseCase(),
            $ids,
            $this->currentPage,
            $this->perPage
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function test_controller_return_value(): void
    {
        $ids = array_column(self::arrayData(), 'id');

        $result = $this->controller->getWorldHeritagesByIds(
            $this->mockUseCase(),
            $ids,
            $this->currentPage,
            $this->perPage
        );

        $response = $result->getOriginalContent();

        $this->assertSame(count($response['data']), count(self::arrayData()));

        foreach ($response['data'] as $key => $value) {
            $this->assertSame($value['id'], self::arrayData()[$key]['id']);
            $this->assertSame($value['unesco_id'], self::arrayData()[$key]['unesco_id']);
            $this->assertSame($value['official_name'], self::arrayData()[$key]['official_name']);
            $this->assertSame($value['name'], self::arrayData()[$key]['name']);
            $this->assertSame($value['country'], self::arrayData()[$key]['country']);
            $this->assertSame($value['region'], self::arrayData()[$key]['region']);
            $this->assertSame($value['category'], self::arrayData()[$key]['category']);
            $this->assertSame($value['year_inscribed'], self::arrayData()[$key]['year_inscribed']);
            $this->assertSame($value['is_endangered'], self::arrayData()[$key]['is_endangered']);
            $this->assertSame($value['latitude'], self::arrayData()[$key]['latitude']);
            $this->assertSame($value['longitude'], self::arrayData()[$key]['longitude']);
            $this->assertSame($value['name_jp'], self::arrayData()[$key]['name_jp']);
            $this->assertSame($value['state_party'], self::arrayData()[$key]['state_party']);
            $this->assertSame($value['criteria'], self::arrayData()[$key]['criteria']);
            $this->assertSame($value['area_hectares'], self::arrayData()[$key]['area_hectares']);
            $this->assertSame($value['buffer_zone_hectares'], self::arrayData()[$key]['buffer_zone_hectares']);
            $this->assertSame($value['short_description'], self::arrayData()[$key]['short_description']);
            $this->assertSame($value['image_url'], self::arrayData()[$key]['image_url']);
            $this->assertSame($value['unesco_site_url'], self::arrayData()[$key]['unesco_site_url']);
        }
    }
}