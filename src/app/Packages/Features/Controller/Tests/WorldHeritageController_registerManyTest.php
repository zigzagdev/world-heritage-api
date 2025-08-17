<?php

namespace App\Packages\Features\Controller\Tests;

use App\Packages\Features\Controller\WorldHeritageController;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageDtoCollectionFactory;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldManyHeritagesUseCase;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Illuminate\Http\JsonResponse;

class WorldHeritageController_registerManyTest extends TestCase
{

    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new WorldHeritageController();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function mockDto(): WorldHeritageDtoCollection
    {
        $factory = Mockery::mock(
            'alias' . WorldHeritageDtoCollectionFactory::class
        );
        $mock = Mockery::mock(WorldHeritageDtoCollection::class);

        $collection = array_map(
            fn (array $data) => new WorldHeritageDto(
                id: $data['id'] ?? null,
                unescoId: $data['unesco_id'],
                officialName: $data['official_name'],
                name: $data['name'],
                country: $data['country'],
                region: $data['region'],
                stateParty: $data['state_party'],
                category: $data['category'],
                criteria: $data['criteria'],
                yearInscribed: $data['year_inscribed'],
                areaHectares: $data['area_hectares'],
                bufferZoneHectares: $data['buffer_zone_hectares'],
                isEndangered: $data['is_endangered'] ?? false,
                latitude: $data['latitude'],
                longitude: $data['longitude'],
                shortDescription: $data['short_description'] ?? null,
                imageUrl: $data['image_url'] ?? null,
                unescoSiteUrl: $data['unesco_site_url'] ?? null
            ), self::arrayData()
        );

        $factory->shouldReceive('build')
            ->with(Mockery::type('array'))
            ->andReturn($mock);

        $mock->shouldReceive('getHeritages')
            ->andReturn($collection);

        return $mock;
    }

    private function mockUseCase(): CreateWorldManyHeritagesUseCase
    {
        $mock = Mockery::mock(CreateWorldManyHeritagesUseCase::class);

        $mock->shouldReceive('handle')
            ->with(Mockery::type('array'))
            ->andReturn($this->mockDto());

        return $mock;
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

    private function mockRequest(): Request
    {
        $request = Mockery::mock(Request::class);

        $request
            ->shouldReceive('all')
            ->andReturn(self::arrayData());

        return $request;
    }

    public function test_check_controller(): void
    {
        $result = $this->controller->registerManyWorldHeritages(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function test_check_controller_value(): void
    {
        $result = $this->controller->registerManyWorldHeritages(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $data = $result->getOriginalContent();

        foreach ($data['data'] as $key => $value) {
            $this->assertEquals(self::arrayData()[$key]['id'], $value['id']);
            $this->assertEquals(self::arrayData()[$key]['unesco_id'], $value['unesco_id']);
            $this->assertEquals(self::arrayData()[$key]['official_name'], $value['official_name']);
            $this->assertEquals(self::arrayData()[$key]['name'], $value['name']);
            $this->assertEquals(self::arrayData()[$key]['country'], $value['country']);
            $this->assertEquals(self::arrayData()[$key]['region'], $value['region']);
            $this->assertEquals(self::arrayData()[$key]['state_party'], $value['state_party']);
            $this->assertEquals(self::arrayData()[$key]['category'], $value['category']);
            $this->assertEquals(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertEquals(self::arrayData()[$key]['year_inscribed'], $value['year_inscribed']);
            $this->assertEquals(self::arrayData()[$key]['area_hectares'], $value['area_hectares']);
            $this->assertEquals(self::arrayData()[$key]['buffer_zone_hectares'], $value['buffer_zone_hectares']);
            $this->assertEquals(self::arrayData()[$key]['is_endangered'], $value['is_endangered']);
            $this->assertEquals(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertEquals(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value['short_description'] ?? null);
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value['image_url'] ?? null);
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value['unesco_site_url'] ?? null);
        }
    }
}