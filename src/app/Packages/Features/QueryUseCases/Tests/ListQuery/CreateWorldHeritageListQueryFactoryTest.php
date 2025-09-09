<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Packages\Features\QueryUseCases\Factory\CreateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;
use DomainException;
use Tests\TestCase;

class CreateWorldHeritageListQueryFactoryTest extends TestCase
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
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => null,
            'country' => 'Slovakia',
            'region' => 'Europe',
            'category' => 'natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'year_inscribed' => 2007,
            'area_hectares' => 99947.81,
            'buffer_zone_hectares' => 296275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
            'state_parties' => [
                'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
            ],
            'state_parties_meta' => [
                'ALB' => ['is_primary' => false, 'inscription_year' => 2007],
                'AUT' => ['is_primary' => false, 'inscription_year' => 2007],
                'BEL' => ['is_primary' => false, 'inscription_year' => 2007],
                'BIH' => ['is_primary' => false, 'inscription_year' => 2007],
                'BGR' => ['is_primary' => false, 'inscription_year' => 2007],
                'HRV' => ['is_primary' => false, 'inscription_year' => 2007],
                'CZE' => ['is_primary' => false, 'inscription_year' => 2007],
                'FRA' => ['is_primary' => false, 'inscription_year' => 2007],
                'DEU' => ['is_primary' => false, 'inscription_year' => 2007],
                'ITA' => ['is_primary' => false, 'inscription_year' => 2007],
                'MKD' => ['is_primary' => false, 'inscription_year' => 2007],
                'POL' => ['is_primary' => false, 'inscription_year' => 2007],
                'ROU' => ['is_primary' => false, 'inscription_year' => 2007],
                'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                'SVN' => ['is_primary' => false, 'inscription_year' => 2007],
                'ESP' => ['is_primary' => false, 'inscription_year' => 2007],
                'CHE' => ['is_primary' => false, 'inscription_year' => 2007],
                'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
            ],
        ];
    }

    private static function wrongArrayData(): array
    {
        return [
            'id' => null,
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
        $result = CreateWorldHeritageListQueryFactory::build(self::arrayData());

        $this->assertInstanceOf(WorldHeritageListQuery::class, $result);
    }

    public function test_check_list_query_value(): void
    {
        $result = CreateWorldHeritageListQueryFactory::build(self::arrayData());

        $this->assertEquals(self::arrayData()['id'], $result->getId());
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
        $this->expectExceptionMessage("id is Required !");

        CreateWorldHeritageListQueryFactory::build(self::wrongArrayData());
    }
}