<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Tests\TestCase;
use Mockery;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageViewModelCollectionFactory;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModelCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageDtoCollectionFactory;

class WorldHeritageViewModelCollectionFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
                'created_at' => now(), 'updated_at' => now(),
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
                'created_at' => now(), 'updated_at' => now(),
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
                'created_at' => now(), 'updated_at' => now(),
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
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];
    }

    private function mockDtoCollection(): WorldHeritageDtoCollection
    {
        $factory = Mockery::mock(
            'alias' . WorldHeritageDtoCollectionFactory::class
        );
        $mock = Mockery::mock(WorldHeritageDtoCollection::class);

        $dtos = array_map(
            fn (array $data) => new WorldHeritageDto(
                id: $data['id'],
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

        $factory
            ->shouldReceive('build')
            ->with(self::arrayData())
            ->andReturn($mock);

        $mock
            ->shouldReceive('getHeritages')
            ->andReturn($dtos);

        return $mock;
    }

    public function test_view_model_collection_check_type(): void
    {
        $result = WorldHeritageViewModelCollectionFactory::build(
            $this->mockDtoCollection()
        );

        $this->assertInstanceOf(
            WorldHeritageViewModelCollection::class,
            $result
        );
    }

    public function test_view_model_collection_check_value(): void
    {
        $result = WorldHeritageViewModelCollectionFactory::build(
            $this->mockDtoCollection()
        );

        foreach ($result->toArray() as $key => $value) {
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
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value['short_description']);
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value['image_url']);
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value['unesco_site_url']);
        }
    }
}