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

    private function arrayData(): array
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
                'area_hectares' => 99_947.81,
                'buffer_zone_hectares' => 296_275.8,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '氷期後のブナの自然拡散史を示すヨーロッパ各地の原生的ブナ林群から成る越境・連続資産。',
                'short_description_jp' => 'あいうえお',
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
            ->andReturn($this->arrayData());

        return $mock;
    }

    private function mockDto(): WorldHeritageDto
    {
        $mock = Mockery::mock(WorldHeritageDto::class);

        $mock->shouldReceive('getId')
            ->andReturn($this->arrayData()['id']);

        $mock->shouldReceive('getOfficialName')
            ->andReturn($this->arrayData()['official_name']);

        $mock->shouldReceive('getName')
            ->andReturn($this->arrayData()['name']);

        $mock->shouldReceive('getCountry')
            ->andReturn($this->arrayData()['country']);

        $mock->shouldReceive('getRegion')
            ->andReturn($this->arrayData()['region']);

        $mock->shouldReceive('getCategory')
            ->andReturn($this->arrayData()['category']);

        $mock->shouldReceive('getYearInscribed')
            ->andReturn($this->arrayData()['year_inscribed']);

        $mock->shouldReceive('getLatitude')
            ->andReturn($this->arrayData()['latitude']);

        $mock->shouldReceive('getLongitude')
            ->andReturn($this->arrayData()['longitude']);

        $mock->shouldReceive('isEndangered')
            ->andReturn($this->arrayData()['is_endangered']);

        $mock->shouldReceive('getHeritageNameJp')
            ->andReturn($this->arrayData()['heritage_name_jp']);

        $mock->shouldReceive('getStateParty')
            ->andReturn($this->arrayData()['state_party']);

        $mock->shouldReceive('getCriteria')
            ->andReturn($this->arrayData()['criteria']);

        $mock->shouldReceive('getUnescoSiteUrl')
            ->andReturn($this->arrayData()['unesco_site_url']);

        $mock->shouldReceive('getAreaHectares')
            ->andReturn($this->arrayData()['area_hectares']);

        $mock->shouldReceive('getBufferZoneHectares')
            ->andReturn($this->arrayData()['buffer_zone_hectares']);

        $mock->shouldReceive('getShortDescription')
            ->andReturn($this->arrayData()['short_description']);

        $mock->shouldReceive('getStatePartyCodes')
            ->andReturn($this->arrayData()['state_parties_codes']);

        $mock->shouldReceive('getStatePartiesMeta')
            ->andReturn($this->arrayData()['state_parties_meta']);

        $mock->shouldReceive('getThumbnailUrl')
            ->andReturn($this->arrayData()['thumbnail_url']);

        $mock->shouldReceive('getCountryNameJp')
            ->andReturn($this->arrayData()['country_name_jp']);

        $mock->shouldReceive('getShortDescriptionJp')
            ->andReturn($this->arrayData()['short_description_jp']);

        $mock->shouldReceive('getPrimaryStatePartyCode')
            ->andReturn($this->arrayData()['state_parties_codes'][4]);

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

        $this->assertEquals($this->arrayData()['id'], $resultArray['id']);
        $this->assertEquals($this->arrayData()['official_name'], $resultArray['official_name']);
        $this->assertEquals($this->arrayData()['name'], $resultArray['name']);
        $this->assertEquals($this->arrayData()['country'], $resultArray['country']);
        $this->assertEquals($this->arrayData()['region'], $resultArray['region']);
        $this->assertEquals($this->arrayData()['category'], $resultArray['category']);
        $this->assertEquals($this->arrayData()['year_inscribed'], $resultArray['year_inscribed']);
        $this->assertEquals($this->arrayData()['latitude'], $resultArray['latitude']);
        $this->assertEquals($this->arrayData()['longitude'], $resultArray['longitude']);
        $this->assertEquals($this->arrayData()['is_endangered'], $resultArray['is_endangered']);
        $this->assertEquals($this->arrayData()['heritage_name_jp'], $resultArray['heritage_name_jp']);
        $this->assertEquals($this->arrayData()['state_party'], $resultArray['state_party']);
        $this->assertEquals($this->arrayData()['criteria'], $resultArray['criteria']);
        $this->assertEquals($this->arrayData()['unesco_site_url'], $resultArray['unesco_site_url']);
        $this->assertEquals($this->arrayData()['area_hectares'], $resultArray['area_hectares']);
        $this->assertEquals($this->arrayData()['buffer_zone_hectares'], $resultArray['buffer_zone_hectares']);
        $this->assertEquals($this->arrayData()['short_description'], $resultArray['short_description']);
        $this->assertEquals($this->arrayData()['short_description_jp'], $resultArray['short_description_jp']);
        $this->assertEquals($this->arrayData()['state_parties_codes'], $resultArray['state_party_codes']);
        $this->assertEquals($this->arrayData()['state_parties_meta'], $resultArray['state_parties_meta']);
        $this->assertEquals($this->arrayData()['thumbnail_url'], $resultArray['thumbnail_url']);
        $this->assertEquals($this->arrayData()['country_name_jp'], $resultArray['country_name_jp']);
    }
}