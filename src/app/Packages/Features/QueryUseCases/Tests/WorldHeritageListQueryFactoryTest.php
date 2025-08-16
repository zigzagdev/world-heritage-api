<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use DomainException;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageListQueryFactory;

class WorldHeritageListQueryFactoryTest extends TestCase
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
            'unesco_id' => '668',
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
        ];
    }

    private static function wrongArrayData(): array
    {
        return [
            'unesco_id' => null,
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => null,
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
        ];
    }

    public function test_check_list_query_type(): void
    {
        $result = WorldHeritageListQueryFactory::build(self::arrayData());

        $this->assertInstanceOf(WorldHeritageListQuery::class, $result);
    }

    public function test_check_list_query_value(): void
    {
        $result = WorldHeritageListQueryFactory::build(self::arrayData());

        $this->assertEquals(self::arrayData()['unesco_id'], $result->getUnescoId());
        $this->assertEquals(self::arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals(self::arrayData()['name'], $result->getName());
        $this->assertEquals(self::arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals(self::arrayData()['country'], $result->getCountry());
        $this->assertEquals(self::arrayData()['region'], $result->getRegion());
        $this->assertEquals(self::arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals(self::arrayData()['category'], $result->getCategory());
        $this->assertEquals(self::arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals(self::arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals(self::arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals(self::arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals(self::arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals(self::arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals(self::arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals(self::arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals(self::arrayData()['image_url'], $result->getImageUrl());
        $this->assertEquals(self::arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
    }

    public function test_check_list_required_is_null(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("unesco_id is Required !");

        WorldHeritageListQueryFactory::build(self::wrongArrayData());
    }
}