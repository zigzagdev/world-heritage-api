<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;

class GetWorldHeritageByIdTest extends TestCase
{
    private $id;
    protected function setUp(): void
    {
        parent::setUp();
        $this->id = self::arrayData()['id'];
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

    public function test_feature_test_ok(): void
    {
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

        $result = $this->getJson("/api/v1/heritages/{$this->id}");

        $result->assertStatus(200);
        $result->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
                'state_party',
                'category',
                'criteria',
                'year_inscribed',
                'area_hectares',
                'buffer_zone_hectares',
                'is_endangered',
                'latitude',
                'longitude',
                'short_description',
                'image_url',
                'unesco_site_url',
            ]
        ]);

        $this->assertEquals($expectedCodes, $result['data']['state_party_codes']);
        $this->assertEquals($orderedExpected, $result['data']['state_parties_meta']);
    }

    public function test_ng_id_is_null(): void
    {
        $this->id = 299;

        $this->getJson("/api/v1/heritages/{$this->id}")
            ->assertStatus(404);
    }
}