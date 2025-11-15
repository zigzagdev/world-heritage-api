<?php

namespace App\Packages\Features\Tests;

use App\Models\Image;
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
                'name_jp' => '姫路城',
                'country' => 'Japan',
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
                'name_jp' => '屋久島',
                'country' => 'Japan',
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
                'name_jp' => '白神山地',
                'country' => 'Japan',
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
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China',
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
        $ids = array_column(self::arrayData(), 'id');

        $result = $this->getJson('/api/v1/heritages?ids=' . implode(',', $ids));
        $result->assertStatus(200);

        $arrayData = $result->getOriginalContent()['data'];

        $expectedCriteria = [
            661 => ['i','iv'],
            662 => ['vii','ix'],
            663 => ['ix','x'],
            1442 => ['ii','iii','vi'],
        ];

        $expectedCodes = [
            661 => ['JPN'],
            662 => ['JPN'],
            663 => ['JPN'],
            1442 => ['CHN','KAZ','KGZ'],
        ];

        $expectedMeta = [
            661 => ['JPN' => ['is_primary' => true,  'inscription_year' => 1993]],
            662 => ['JPN' => ['is_primary' => true,  'inscription_year' => 1993]],
            663 => ['JPN' => ['is_primary' => true,  'inscription_year' => 1993]],
            1442 => [
                'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
            ],
        ];

        $expectedPrimary = [];
        foreach ($expectedMeta as $id => $metaByCode) {
            $primary = null;

            foreach ($metaByCode as $code => $row) {
                if (!empty($row['is_primary'])) {
                    $primary = $code;
                    break;
                }
            }
            if ($primary === null && !empty($expectedCodes[$id]) && count($expectedCodes[$id]) === 1) {
                $primary = $expectedCodes[$id][0];
            }
            $expectedPrimary[$id] = $primary;
        }

        $expectedById = [];
        foreach (self::arrayData() as $row) {
            $expectedById[$row['id']] = $row;
        }

        foreach ($arrayData['data'] as $value) {
            $this->assertArrayHasKey('id', $value);
            $this->assertArrayHasKey($value['id'], $expectedById);

            $expected = $expectedById[$value['id']];

            $this->assertEquals($expected['id'], $value['id']);
            $this->assertEquals($expected['official_name'], $value['official_name']);
            $this->assertEquals($expected['name'], $value['name']);
            $this->assertEquals($expected['name_jp'], $value['name_jp']);
            $this->assertEquals($expected['country'], $value['country']);
            $this->assertEquals($expected['region'], $value['region']);
            $this->assertEquals($expected['category'], $value['category']);
            $this->assertEquals($expected['year_inscribed'], $value['year_inscribed']);
            $this->assertEquals($expected['state_party'] ?? null, $value['state_party'] ?? null);
            $this->assertEquals($expected['area_hectares'], $value['area_hectares']);
            $this->assertEquals($expected['buffer_zone_hectares'], $value['buffer_zone_hectares']);
            $this->assertEquals($expected['is_endangered'], $value['is_endangered']);
            $this->assertEquals($expected['latitude'], $value['latitude']);
            $this->assertEquals($expected['longitude'], $value['longitude']);
            $this->assertEquals($expected['short_description'], $value['short_description']);

            if (array_key_exists('unesco_siteUrl', $value)) {
                $this->assertEquals($expected['unesco_site_url'], $value['unesco_siteUrl']);
            } else {
                $this->assertEquals($expected['unesco_site_url'], $value['unesco_site_url']);
            }

            $this->assertEqualsCanonicalizing(
                $expectedCriteria[(int)$value['id']],
                $value['criteria']
            );

            $codes = $value['state_party_code'] ?? ($value['state_party_codes'] ?? null);
            $codes = $codes ?? [];
            $this->assertEqualsCanonicalizing(
                $expectedCodes[(int)$value['id']],
                $codes
            );

            $this->assertArrayHasKey('state_parties_meta', $value);
            $this->assertEqualsCanonicalizing(
                array_keys($expectedMeta[(int)$value['id']]),
                array_keys($value['state_parties_meta']),
                "state_parties_meta keys mismatch for id={$value['id']}"
            );

            $thumbUrl = $value['thumbnail_url'] ?? ($value['thumbnail'] ?? null);

            $this->assertIsString($thumbUrl);
            $this->assertNotEmpty($thumbUrl);
            $this->assertMatchesRegularExpression(
                '#^https?://[^/]+/storage/world_heritage/'.$value['id'].'/img\d+\.(jpg|jpeg|png)$#',
                $thumbUrl,
                "thumbnail url format mismatch for id={$value['id']}"
            );
        }
    }

    public function test_feature_api_ok_without_ids(): void
    {
        $result = $this->getJson('/api/v1/heritages');

        $result->assertStatus(200);
    }
}
