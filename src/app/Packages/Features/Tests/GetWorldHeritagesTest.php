<?php

namespace App\Packages\Features\Tests;

use App\Models\Image;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;

class GetWorldHeritagesTest extends TestCase
{
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
                'id' => '661',
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'heritage_name_jp' => '姫路城',
                'country' => 'Japan',
                'country_name_jp' => '日本',
                'region' => 'Asia',
                'state_party' => 'JPN',
                'category' => 'Cultural',
                'criteria' => ['i','iv'],
                'year_inscribed' => 1993,
                'area_hectares' => 107.0,
                'buffer_zone_hectares' => 143.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "白鷺城の名で知られる城郭建築の傑作。天守群と縄張りが良好に保存される。",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661',
            ],
            [
                'id' => '662',
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'heritage_name_jp' => '屋久島',
                'country' => 'Japan',
                'country_name_jp' => '日本',
                'region' => 'Asia',
                'state_party' => 'JPN',
                'category' => 'Natural',
                'criteria' => ['vii','ix'],
                'year_inscribed' => 1993,
                'area_hectares' => 10747.0,
                'buffer_zone_hectares' => null,
                'is_endangered' => false,
                'latitude' => null,
                'longitude' => null,
                'short_description' => "巨樹・照葉樹林に代表される生態系と景観が特筆される島。",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662',
            ],
            [
                'id' => '663',
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'heritage_name_jp' => '白神山地',
                'country' => 'Japan',
                'country_name_jp' => '日本',
                'region' => 'Asia',
                'state_party' => 'JPN',
                'category' => 'Natural',
                'criteria' => ['ix','x'],
                'year_inscribed' => 1993,
                'area_hectares' => 16971.0,
                'buffer_zone_hectares' => 6832.0,
                'is_endangered' => false,
                'latitude' => null,
                'longitude' => null,
                'short_description' => "日本最大級のブナ天然林を中心とする山地生態系。",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663',
            ],
            [
                'id' => '1442',
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk·Roads:·Chang'an–Tianshan·Corridor",
                'heritage_name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China',
                'country_name_jp' => '中国',
                'region' => 'Asia',
                'state_party' => null,
                'category' => 'Cultural',
                'criteria' => ['ii','iii','vi'],
                'year_inscribed' => 2014,
                'area_hectares' => 42668.16,
                'buffer_zone_hectares' => 189963.1,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '中国・カザフスタン・キルギスにまたがるオアシス都市や遺跡群で構成され、東西交流の歴史を物証する文化遺産群。',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442',
            ]
        ];
    }

    public function test_feature_api_ok_with_ids(): void
    {
        $result = $this->getJson('/api/v1/heritages');

        $result->assertStatus(200);
        $result->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'items' => [
                        '*' => [
                        'id',
                        'official_name',
                        'name',
                        'heritage_name_jp',
                        'country',
                        'country_name_jp',
                        'region',
                        'category',
                        'year_inscribed',
                        'latitude',
                        'longitude',
                        'is_endangered',
                        'state_party',
                        'criteria',
                        'area_hectares',
                        'buffer_zone_hectares',
                        'short_description',
                        'unesco_site_url',
                        'state_party_codes',
                        'state_parties_meta',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ]);
    }

    public function test_feature_api_ok_without_ids(): void
    {
        $result = $this->getJson('/api/v1/heritages');

        $result->assertStatus(200);
    }
}
