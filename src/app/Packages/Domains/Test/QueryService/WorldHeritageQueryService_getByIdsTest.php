<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Common\Pagination\PaginationDto;
use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;

class WorldHeritageQueryService_getByIdsTest extends TestCase
{
    private $queryService;
    private int $perPage;
    private int $currentPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->perPage = 15;
        $this->currentPage = 1;
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
            DB::table('site_state_parties')->truncate();
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return [
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
            ],
            [
                'id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk·Roads:·Chang'an–Tianshan·Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China',
                'region' => 'Asia',
                'category' => 'Cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 42668.16,
                'buffer_zone_hectares' => 189963.1,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '中国・カザフスタン・キルギスにまたがるオアシス都市や遺跡群で構成され、東西交流の歴史を物証する文化遺産群。',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442',
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    public function test_fetch_data_check_type(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $result = $this->queryService->getHeritagesByIds(
            $ids,
            $this->currentPage,
            $this->perPage
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
    }

    public function test_fetch_data_check_value(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $result = $this->queryService->getHeritagesByIds(
            $ids,
            $this->currentPage,
            $this->perPage
        );

        foreach ($result->toArray()['data'] as $key => $value) {
            $this->assertSame(self::arrayData()[$key]['id'], $value['id']);
            $this->assertSame(self::arrayData()[$key]['official_name'], $value['officialName']);
            $this->assertSame(self::arrayData()[$key]['name'], $value['name']);
            $this->assertSame(self::arrayData()[$key]['name_jp'], $value['nameJp']);
            $this->assertSame(self::arrayData()[$key]['country'], $value['country']);
            $this->assertSame(self::arrayData()[$key]['region'], $value['region']);
            $this->assertSame(self::arrayData()[$key]['state_party'], $value['stateParty']);
            $this->assertSame(self::arrayData()[$key]['category'], $value['category']);
            $this->assertSame(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertSame(self::arrayData()[$key]['year_inscribed'], $value['yearInscribed']);
            $this->assertSame(self::arrayData()[$key]['area_hectares'], $value['areaHectares']);
            $this->assertSame(self::arrayData()[$key]['buffer_zone_hectares'], $value['bufferZoneHectares']);
            $this->assertSame(self::arrayData()[$key]['is_endangered'], $value['isEndangered']);
            $this->assertSame(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertSame(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertSame(self::arrayData()[$key]['short_description'], $value['shortDescription']);
            $this->assertSame(self::arrayData()[$key]['unesco_site_url'], $value['unescoSiteUrl']);
            $this->assertArrayHasKey('statePartyCodes', $value);
            $this->assertArrayHasKey('statePartiesMeta', $value);
            $this->assertArrayHasKey('thumbnail', $value);
        }

        $expectedFirstCodes = [
            'ALB','AUT','BEL','BGR','BIH','CHE','CZE','DEU','ESP','FRA',
            'HRV','ITA','MKD','POL','ROU','SVK','SVN','UKR',
        ];
        $expectedFirst = [
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
        $orderedExpectedFirst = [];
        foreach ($expectedFirstCodes as $code) {
            $orderedExpectedFirst[$code] = $expectedFirst[$code];
        }

        $this->assertSame(
            $expectedFirstCodes,
            $result->toArray()['data'][0]['statePartyCodes']
        );
        $this->assertSame(
            $orderedExpectedFirst,
            $result->toArray()['data'][0]['statePartiesMeta']
        );

        $expectedSecondCodes = ['CHN','KAZ','KGZ'];
        $expectedSecond = [
            'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
            'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
            'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
        ];
        $orderedExpectedSecond = [];
        foreach ($expectedSecondCodes as $code) {
            $orderedExpectedSecond[$code] = $expectedSecond[$code];
        }

        $this->assertSame(
            $expectedSecondCodes,
            $result->toArray()['data'][1]['statePartyCodes']
        );
        $this->assertSame(
            $orderedExpectedSecond,
            $result->toArray()['data'][1]['statePartiesMeta']
        );
    }
}