<?php

namespace App\Packages\Features\Tests;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;
use Database\Seeders\DatabaseSeeder;

class UpdateOneWorldHeritageTest extends TestCase
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
        return [
            'id' => 1418,
            'official_name' => 'Fujisan, sacred place and source of artistic inspiration',
            'name' => 'Fujisan',
            'name_jp' => '富士山—信仰の対象と芸術の源泉(更新をした。)',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => null,
            'category' => 'Cultural',
            'criteria' => ['iii', 'vi'],
            'year_inscribed' => 2013,
            'area_hectares' => 122334.0,
            'buffer_zone_hectares' => 0.0,
            'is_endangered' => false,
            'latitude' => 35.3606,
            'longitude' => 138.7274,
            'short_description' => "Mount Fuji is the highest mountain in Japan, standing 3,776 meters tall. An active stratovolcano that last erupted in 1707–1708, Mount Fuji lies about 100 kilometers southwest of Tokyo and is visible from there on clear days. Mount Fuji's exceptionally symmetrical cone, which is snow-capped for about five months a year, is a well-known symbol of Japan and it is frequently depicted in art and photographs, as well as visited by sightseers and climbers.",
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418/',
            'state_parties' => ['FRA'],
            'state_parties_meta' => [
                'FRA' => [
                    'is_primary' => true,
                    'inscription_year' => 5000,
                ],
            ],
        ];
    }

    public function test_feature(): void
    {

        $intId = intval(self::arrayData()['id']);

        $initialHeritage = WorldHeritage::find($intId);
        $beforeNameJp = $initialHeritage->name_jp;

        $result = $this->putJson("/api/v1/heritages/{$intId}", self::arrayData());

        $result->assertStatus(200);
        $result->assertJsonStructure([
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
                'state_party_codes',
                'state_parties_meta' => [
                    'FRA' => [
                        'is_primary',
                        'inscription_year',
                    ],
                ],
            ],
        ]);
        $this->assertNotSame($beforeNameJp, $result['data']['name_jp']);
    }
}