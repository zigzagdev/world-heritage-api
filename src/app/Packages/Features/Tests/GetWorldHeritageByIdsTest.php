<?php

namespace App\Packages\Features\Tests;

use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;

class GetWorldHeritageByIdsTest extends TestCase
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
            [
                'id' => 1,
                'unesco_id' => '660',
                'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name_jp' => '法隆寺地域の仏教建造物',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6147,
                'longitude' => 135.7355,
                'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
            ],
            [
                'id' => 2,
                'unesco_id' => '661',
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'name_jp' => '姫路城',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "A masterpiece of Japanese castle architecture in original form.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661/',
            ],
            [
                'id' => 3,
                'unesco_id' => '662',
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'name_jp' => '屋久島',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 30.3581,
                'longitude' => 130.546,
                'short_description' => "A subtropical island with ancient cedar forests and diverse ecosystems.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662/',
            ],
            [
                'id' => 4,
                'unesco_id' => '663',
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'name_jp' => '白神山地',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 40.5167,
                'longitude' => 140.05,
                'short_description' => "Pristine beech forest with minimal human impact.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663/',
            ]
        ];
    }

    public function test_feature_api_ok(): void
    {
        $ids = array_column(self::arrayData(), 'id');

        $result = $this->getJson(
            '/api/v1/heritages?ids=' . implode(',', $ids)
        );
        $result->assertStatus(200);
        $arrayData = $result->getOriginalContent()['data']['data'];
        foreach ($arrayData as $key => $value) {
            $this->assertEquals(self::arrayData()[$key]['id'], $value['id']);
            $this->assertEquals(self::arrayData()[$key]['unesco_id'], $value['unescoId']);
            $this->assertEquals(self::arrayData()[$key]['official_name'], $value['officialName']);
            $this->assertEquals(self::arrayData()[$key]['name'], $value['name']);
            $this->assertEquals(self::arrayData()[$key]['name_jp'], $value['nameJp']);
            $this->assertEquals(self::arrayData()[$key]['country'], $value['country']);
            $this->assertEquals(self::arrayData()[$key]['region'], $value['region']);
            $this->assertEquals(self::arrayData()[$key]['state_party'], $value['stateParty']);
            $this->assertEquals(self::arrayData()[$key]['category'], $value['category']);
            $this->assertEquals(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertEquals(self::arrayData()[$key]['year_inscribed'], $value['yearInscribed']);
            $this->assertEquals(self::arrayData()[$key]['area_hectares'], $value['areaHectares']);
            $this->assertEquals(self::arrayData()[$key]['buffer_zone_hectares'], $value['bufferZoneHectares']);
            $this->assertEquals(self::arrayData()[$key]['is_endangered'], $value['isEndangered']);
            $this->assertEquals(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertEquals(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value['shortDescription']);
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value['imageUrl']);
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value['unescoSiteUrl']);
        }
    }
}