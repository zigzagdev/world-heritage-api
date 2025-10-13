<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Image;

class WorldHeritageQueryService_getByIdTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->repository = app(WorldHeritageQueryService::class);
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
            Image::truncate();
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

    public function test_repository_check(): void
    {
        $result = $this->repository->getHeritageById($this->arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_check_data_value(): void
    {
        $result = $this->repository->getHeritageById($this->arrayData()['id']);

        $expectedCodes = [
            'ALB','AUT','BEL','BGR','BIH','CHE','CZE','DEU','ESP','FRA',
            'HRV','ITA','MKD','POL','ROU','SVK','SVN','UKR',
        ];

        $expected = [
            'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
            'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
            'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
            'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
            'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
            'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
            'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
            'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
            'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
            'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
            'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
            'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
            'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
            'POL' => ['is_primary' => false, 'inscription_year' => 2021],
            'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
            'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
            'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
            'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
        ];

        $orderedExpected = [];
        foreach ($expectedCodes as $code) {
            $orderedExpected[$code] = $expected[$code];
        }

        $this->assertEquals($this->arrayData()['id'], $result->getId());
        $this->assertEquals($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals($this->arrayData()['name'], $result->getName());
        $this->assertEquals($this->arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals($this->arrayData()['country'], $result->getCountry());
        $this->assertEquals($this->arrayData()['region'], $result->getRegion());
        $this->assertEquals($this->arrayData()['category'], $result->getCategory());
        $this->assertEquals($this->arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals($this->arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals($this->arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals($this->arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals($this->arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals($this->arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals($this->arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals($this->arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals($this->arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals($this->arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals($expectedCodes, $result->getStatePartyCodes());
        $this->assertEquals($orderedExpected, $result->getStatePartiesMeta());
        $this->assertNotNull($result->toArray()['images']);
    }
}