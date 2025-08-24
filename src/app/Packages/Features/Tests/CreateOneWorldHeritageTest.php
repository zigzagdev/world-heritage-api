<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateOneWorldHeritageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
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
            'id' => 1,
            'unesco_id' => '1133',
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
                'AL','AT','BE','BA','BG','HR','CZ','FR','DE','IT','MK','PL','RO','SK','SI','ES','CH','UA'
            ],
            'state_parties_meta' => [
                'AL' => ['is_primary' => false, 'inscription_year' => 2007],
                'AT' => ['is_primary' => false, 'inscription_year' => 2007],
                'BE' => ['is_primary' => false, 'inscription_year' => 2007],
                'BA' => ['is_primary' => false, 'inscription_year' => 2007],
                'BG' => ['is_primary' => false, 'inscription_year' => 2007],
                'HR' => ['is_primary' => false, 'inscription_year' => 2007],
                'CZ' => ['is_primary' => false, 'inscription_year' => 2007],
                'FR' => ['is_primary' => false, 'inscription_year' => 2007],
                'DE' => ['is_primary' => false, 'inscription_year' => 2007],
                'IT' => ['is_primary' => false, 'inscription_year' => 2007],
                'MK' => ['is_primary' => false, 'inscription_year' => 2007],
                'PL' => ['is_primary' => false, 'inscription_year' => 2007],
                'RO' => ['is_primary' => false, 'inscription_year' => 2007],
                'SK' => ['is_primary' => true,  'inscription_year' => 2007],
                'SI' => ['is_primary' => false, 'inscription_year' => 2007],
                'ES' => ['is_primary' => false, 'inscription_year' => 2007],
                'CH' => ['is_primary' => false, 'inscription_year' => 2007],
                'UA' => ['is_primary' => false, 'inscription_year' => 2007],
            ],
        ];
    }

    public function test_feature_check(): void
    {
        $result = $this->postJson('/api/v1/heritage', self::arrayData());

        $result->assertStatus(201);
        $result->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'unesco_id',
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
                    '*' => [
                        'is_primary',
                        'inscription_year',
                    ],
                ],
            ],
        ]);
    }
}