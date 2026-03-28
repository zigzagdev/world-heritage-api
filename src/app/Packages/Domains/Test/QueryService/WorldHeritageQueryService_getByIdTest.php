<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Image;
use App\Packages\Domains\Ports\SignedUrlPort;
use Mockery;

class WorldHeritageQueryService_getByIdTest extends TestCase
{
    private $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $this->app->bind(WorldHeritageSearchPort::class, function () {
            return new class implements WorldHeritageSearchPort {
                public function search($query, int $currentPage, int $perPage): HeritageSearchResult {
                    return new HeritageSearchResult(ids: [], total: 0, currentPage: 1, perPage: $perPage, lastPage: 0);
                }
            };
        });

        $this->queryService = app(WorldHeritageQueryService::class);
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

    private function arrayData(): array
    {
        return
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'heritage_name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
                'country' => 'Slovakia',
                'study_region' => 'Europe',
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
                    'ALB' => ['is_primary' => false],
                    'AUT' => ['is_primary' => false],
                    'BEL' => ['is_primary' => false],
                    'BIH' => ['is_primary' => false],
                    'BGR' => ['is_primary' => false],
                    'HRV' => ['is_primary' => false],
                    'CZE' => ['is_primary' => false],
                    'FRA' => ['is_primary' => false],
                    'DEU' => ['is_primary' => false],
                    'ITA' => ['is_primary' => false],
                    'MKD' => ['is_primary' => false],
                    'POL' => ['is_primary' => false],
                    'ROU' => ['is_primary' => false],
                    'SVK' => ['is_primary' => true],
                    'SVN' => ['is_primary' => false],
                    'ESP' => ['is_primary' => false],
                    'CHE' => ['is_primary' => false],
                    'UKR' => ['is_primary' => false],
                ]
            ];
    }

    public function test_queryService_check(): void
    {
        $result = $this->queryService->getHeritageById($this->arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_check_data_value(): void
    {
        $result = $this->queryService->getHeritageById($this->arrayData()['id']);

        $expectedCodes = [
            'ALB','AUT','BEL','BGR','BIH','CHE','CZE','DEU','ESP','FRA',
            'HRV','ITA','MKD','POL','ROU','SVK','SVN','UKR',
        ];

        $expected = [
            'ALB' => ['is_primary' => false],
            'AUT' => ['is_primary' => false],
            'BEL' => ['is_primary' => false],
            'BGR' => ['is_primary' => false],
            'BIH' => ['is_primary' => false],
            'CHE' => ['is_primary' => false],
            'CZE' => ['is_primary' => false],
            'DEU' => ['is_primary' => false],
            'ESP' => ['is_primary' => false],
            'FRA' => ['is_primary' => false],
            'HRV' => ['is_primary' => false],
            'ITA' => ['is_primary' => false],
            'MKD' => ['is_primary' => false],
            'POL' => ['is_primary' => false],
            'ROU' => ['is_primary' => false],
            'SVK' => ['is_primary' => true ],
            'SVN' => ['is_primary' => false],
            'UKR' => ['is_primary' => false],
        ];

        $orderedExpected = [];
        foreach ($expectedCodes as $code) {
            $orderedExpected[$code] = $expected[$code];
        }

        $this->assertEquals($this->arrayData()['id'], $result->getId());
        $this->assertEquals($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals($this->arrayData()['name'], $result->getName());
        $this->assertEquals($this->arrayData()['heritage_name_jp'], $result->getHeritageNameJp());
        $this->assertEquals($this->arrayData()['country'], $result->getCountry());
        $this->assertEquals($this->arrayData()['study_region'], $result->getRegion());
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
        foreach ($result->getImages() as $img) {
            $this->assertArrayHasKey('id', $img);
            $this->assertArrayHasKey('url', $img);
            $this->assertArrayHasKey('sort_order', $img);
            $this->assertArrayHasKey('is_primary', $img);
            $this->assertIsBool($img['is_primary']);
            $this->assertNotEmpty($img['url']);
        }
    }
}