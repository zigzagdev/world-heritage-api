<?php

namespace App\Packages\Features\QueryUseCases\Tests\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
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
                'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
                'country' => 'Slovakia',
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

        $mock->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

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

        return $mock;
    }

    public function test_check_view_model_type(): void
    {
        $result = WorldHeritageSummaryViewModelFactory::build($this->mockDto());

        $this->assertIsArray($result);
    }

    public function test_check_view_model_value(): void
    {
        $result = WorldHeritageSummaryViewModelFactory::build($this->mockDto());

        $this->assertEquals(self::arrayData()['id'], $result['id']);
        $this->assertEquals(self::arrayData()['official_name'], $result['official_name']);
        $this->assertEquals(self::arrayData()['name'], $result['name']);
        $this->assertEquals(self::arrayData()['country'], $result['country']);
        $this->assertEquals(self::arrayData()['region'], $result['region']);
        $this->assertEquals(self::arrayData()['category'], $result['category']);
        $this->assertEquals(self::arrayData()['year_inscribed'], $result['year_inscribed']);
        $this->assertEquals(self::arrayData()['latitude'], $result['latitude']);
        $this->assertEquals(self::arrayData()['longitude'], $result['longitude']);
        $this->assertEquals(self::arrayData()['is_endangered'], $result['is_endangered']);
        $this->assertEquals(self::arrayData()['name_jp'], $result['name_jp']);
        $this->assertEquals(self::arrayData()['state_party'], $result['state_party']);
        $this->assertEquals(self::arrayData()['criteria'], $result['criteria']);
        $this->assertEquals(self::arrayData()['unesco_site_url'], $result['unesco_site_url']);
        $this->assertEquals(self::arrayData()['area_hectares'], $result['area_hectares']);
        $this->assertEquals(self::arrayData()['buffer_zone_hectares'], $result['buffer_zone_hectares']);
        $this->assertEquals(self::arrayData()['short_description'], $result['short_description']);
        $this->assertEquals(self::arrayData()['state_parties_codes'], $result['state_party_codes']);
        $this->assertEquals(self::arrayData()['state_parties_meta'], $result['state_parties_meta']);
        $this->assertEquals(self::arrayData()['thumbnail_url'], $result['thumbnail_url']);
    }
}