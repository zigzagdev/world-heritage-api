<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\WorldHeritage;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class GetWorldHeritageByIdDtoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
             WorldHeritage::truncate();
             Country::truncate();
             DB::table('site_state_parties')->truncate();
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return
            [
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
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1133',
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

    public function test_dto_check_type(): void
    {
        $heritageDto = new WorldHeritageDto(
            id: self::arrayData()['id'],
            officialName: self::arrayData()['official_name'],
            name: self::arrayData()['name'],
            country: self::arrayData()['country'],
            region: self::arrayData()['region'],
            category: self::arrayData()['category'],
            yearInscribed: self::arrayData()['year_inscribed'],
            latitude: self::arrayData()['latitude'],
            longitude: self::arrayData()['longitude'],
            isEndangered: self::arrayData()['is_endangered'],
            nameJp: self::arrayData()['name_jp'],
            stateParty: self::arrayData()['state_party'],
            criteria: self::arrayData()['criteria'],
            areaHectares: self::arrayData()['area_hectares'],
            bufferZoneHectares: self::arrayData()['buffer_zone_hectares'],
            shortDescription: self::arrayData()['short_description'],
            imageUrl: self::arrayData()['image_url'],
            unescoSiteUrl: self::arrayData()['unesco_site_url'],
            statePartyCodes: self::arrayData()['state_parties_codes'],
            statePartiesMeta: self::arrayData()['state_parties_meta']
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $heritageDto);
    }

    public function test_dto_check_value_type(): void
    {
        $heritageDto = new WorldHeritageDto(
            id: self::arrayData()['id'],
            officialName: self::arrayData()['official_name'],
            name: self::arrayData()['name'],
            country: self::arrayData()['country'],
            region: self::arrayData()['region'],
            category: self::arrayData()['category'],
            yearInscribed: self::arrayData()['year_inscribed'],
            latitude: self::arrayData()['latitude'],
            longitude: self::arrayData()['longitude'],
            isEndangered: self::arrayData()['is_endangered'],
            nameJp: self::arrayData()['name_jp'],
            stateParty: self::arrayData()['state_party'],
            criteria: self::arrayData()['criteria'],
            areaHectares: self::arrayData()['area_hectares'],
            bufferZoneHectares: self::arrayData()['buffer_zone_hectares'],
            shortDescription: self::arrayData()['short_description'],
            imageUrl: self::arrayData()['image_url'],
            unescoSiteUrl: self::arrayData()['unesco_site_url'],
            statePartyCodes: self::arrayData()['state_parties_codes'],
            statePartiesMeta: self::arrayData()['state_parties_meta']
        );

        $this->assertIsInt($heritageDto->getId());
        $this->assertIsString($heritageDto->getOfficialName());
        $this->assertIsString($heritageDto->getName());
        $this->assertIsString($heritageDto->getCountry());
        $this->assertIsString($heritageDto->getRegion());
        $this->assertIsString($heritageDto->getCategory());
        $this->assertIsInt($heritageDto->getYearInscribed());
        $this->assertIsBool($heritageDto->isEndangered());
        $this->assertIsFloat($heritageDto->getLatitude());
        $this->assertIsFloat($heritageDto->getLongitude());
        $this->assertIsString($heritageDto->getNameJp());
        $this->assertIsArray($heritageDto->getCriteria());
        $this->assertIsFloat($heritageDto->getAreaHectares());
        $this->assertIsFloat($heritageDto->getBufferZoneHectares());
        $this->assertIsString($heritageDto->getShortDescription());
        $this->assertIsString($heritageDto->getImageUrl());
        $this->assertIsString($heritageDto->getUnescoSiteUrl());
        $this->assertIsArray($heritageDto->getStatePartyCodes());
        $this->assertIsArray($heritageDto->getStatePartiesMeta());
    }
}