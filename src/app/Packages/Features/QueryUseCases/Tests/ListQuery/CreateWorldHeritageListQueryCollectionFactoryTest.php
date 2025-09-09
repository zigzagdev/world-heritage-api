<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Features\QueryUseCases\Factory\CreateWorldHeritageListQueryCollectionFactory;
use Tests\TestCase;

class CreateWorldHeritageListQueryCollectionFactoryTest extends TestCase
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
                'id' => 660,
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
                'id' => 661,
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
                'id' => 662,
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
                'id' => 663,
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

    public function test_list_query_collection_test_check_type(): void
    {
        $result = CreateWorldHeritageListQueryCollectionFactory::build(self::arrayData());

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $result);
    }

    public function test_list_query_collection_test_check_value(): void
    {
        $result = CreateWorldHeritageListQueryCollectionFactory::build(self::arrayData());

        foreach ($result->getAllHeritages() as $key => $value) {
            $this->assertEquals(self::arrayData()[$key]['id'], $value->getId());
            $this->assertEquals(self::arrayData()[$key]['official_name'], $value->getOfficialName());
            $this->assertEquals(self::arrayData()[$key]['name'], $value->getName());
            $this->assertEquals(self::arrayData()[$key]['name_jp'], $value->getNameJp());
            $this->assertEquals(self::arrayData()[$key]['country'], $value->getCountry());
            $this->assertEquals(self::arrayData()[$key]['region'], $value->getRegion());
            $this->assertEquals(self::arrayData()[$key]['state_party'], $value->getStateParty());
            $this->assertEquals(self::arrayData()[$key]['category'], $value->getCategory());
            $this->assertEquals(self::arrayData()[$key]['criteria'], $value->getCriteria());
            $this->assertEquals(self::arrayData()[$key]['year_inscribed'], $value->getYearInscribed());
            $this->assertEquals(self::arrayData()[$key]['area_hectares'], $value->getAreaHectares());
            $this->assertEquals(self::arrayData()[$key]['buffer_zone_hectares'], $value->getBufferZoneHectares());
            $this->assertEquals(self::arrayData()[$key]['is_endangered'], $value->isEndangered());
            $this->assertEquals(self::arrayData()[$key]['latitude'], $value->getLatitude());
            $this->assertEquals(self::arrayData()[$key]['longitude'], $value->getLongitude());
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value->getShortDescription());
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value->getImageUrl());
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value->getUnescoSiteUrl());
        }
    }
}