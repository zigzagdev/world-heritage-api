<?php

namespace App\Packages\Features\QueryUseCases\Tests\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use Tests\TestCase;
use Mockery;
use App\Packages\Features\QueryUseCases\Factory\ViewModel\WorldHeritageSummaryViewModelFactory;

class WorldHeritageSummaryViewModelFactoryTest extends TestCase
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
                'heritage_name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
                'country' => 'Slovakia',
                'country_name_jp' => 'スロバキア',
                'region' => 'Europe',
                'category' => 'Natural',
                'criteria' => ['ix'],
                'state_party' => null,
                'year_inscribed' => 2007,
                'area_hectares' => 99947.81,
                'buffer_zone_hectares' => 296275.8,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '氷期後のブナの自然拡散史を示すヨーロッパ各地の原生的ブナ林群から成る越境・連続資産。',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1133',
                'thumbnail_url' => 'https://example.com/thumbnail.jpg',
                'state_parties_codes' => [
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
                ]
        ];
    }

    private function mockDtoCollection(): WorldHeritageDtoCollection
    {
        $mock = Mockery::mock(WorldHeritageDtoCollection::class);

        $mock->shouldReceive('toArray')
            ->andReturn(self::arrayData());

        return $mock;
    }

    private function mockDto(): WorldHeritageDto
    {
        $mock = Mockery::mock(WorldHeritageDto::class);

        $mock->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $mock->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $mock->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $mock->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $mock->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $mock->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $mock->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $mock->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $mock->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $mock->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $mock->shouldReceive('getHeritageNameJp')
            ->andReturn(self::arrayData()['heritage_name_jp']);

        $mock->shouldReceive('getStateParty')
            ->andReturn(self::arrayData()['state_party']);

        $mock->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $mock->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        $mock->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $mock->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $mock->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $mock->shouldReceive('getStatePartyCodes')
            ->andReturn(self::arrayData()['state_parties_codes']);

        $mock->shouldReceive('getStatePartiesMeta')
            ->andReturn(self::arrayData()['state_parties_meta']);

        $mock->shouldReceive('getThumbnailUrl')
            ->andReturn(self::arrayData()['thumbnail_url']);

        $mock->shouldReceive('getCountryNameJp')
            ->andReturn(self::arrayData()['country_name_jp']);

        $mock->shouldReceive('getPrimaryStatePartyCode')
            ->andReturn(self::arrayData()['state_parties_codes'][4]);

        $mock->shouldReceive('getImages')
            ->andReturn([
                [
                    'id' => 10,
                    'url' => 'https://cdn.example.com/a.jpg',
                    'sort_order' => 1,
                    'is_primary' => true,
                ],
                [
                    'id' => 11,
                    'url' => 'https://cdn.example.com/b.jpg',
                    'sort_order' => 5,
                    'is_primary' => false,
                ],
            ]);

        return $mock;
    }

    public function test_check_view_model_type(): void
    {
        $result = WorldHeritageSummaryViewModelFactory::build($this->mockDto());

        $this->assertInstanceOf(WorldHeritageViewModel::class, $result);
    }

    public function test_check_view_model_value(): void
    {
        $result = WorldHeritageSummaryViewModelFactory::build($this->mockDto());

        $resultArray = $result->toArray();

        $this->assertEquals(self::arrayData()['id'], $resultArray['id']);
        $this->assertEquals(self::arrayData()['official_name'], $resultArray['official_name']);
        $this->assertEquals(self::arrayData()['name'], $resultArray['name']);
        $this->assertEquals(self::arrayData()['country'], $resultArray['country']);
        $this->assertEquals(self::arrayData()['region'], $resultArray['region']);
        $this->assertEquals(self::arrayData()['category'], $resultArray['category']);
        $this->assertEquals(self::arrayData()['year_inscribed'], $resultArray['year_inscribed']);
        $this->assertEquals(self::arrayData()['latitude'], $resultArray['latitude']);
        $this->assertEquals(self::arrayData()['longitude'], $resultArray['longitude']);
        $this->assertEquals(self::arrayData()['is_endangered'], $resultArray['is_endangered']);
        $this->assertEquals(self::arrayData()['heritage_name_jp'], $resultArray['heritage_name_jp']);
        $this->assertEquals(self::arrayData()['state_party'], $resultArray['state_party']);
        $this->assertEquals(self::arrayData()['criteria'], $resultArray['criteria']);
        $this->assertEquals(self::arrayData()['unesco_site_url'], $resultArray['unesco_site_url']);
        $this->assertEquals(self::arrayData()['area_hectares'], $resultArray['area_hectares']);
        $this->assertEquals(self::arrayData()['buffer_zone_hectares'], $resultArray['buffer_zone_hectares']);
        $this->assertEquals(self::arrayData()['short_description'], $resultArray['short_description']);
        $this->assertEquals(self::arrayData()['state_parties_codes'], $resultArray['state_party_codes']);
        $this->assertEquals(self::arrayData()['state_parties_meta'], $resultArray['state_parties_meta']);
        $this->assertEquals(self::arrayData()['thumbnail_url'], $resultArray['thumbnail_url']);
        $this->assertEquals(self::arrayData()['country_name_jp'], $resultArray['country_name_jp']);
    }
}