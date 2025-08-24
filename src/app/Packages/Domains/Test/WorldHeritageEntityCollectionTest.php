<?php

namespace App\Packages\Domains\Test;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityCollectionTest extends TestCase
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

    private static function arraySingleData(): array
    {
        return [
            [
                'id' => 1,
                'unesco_id' => '668',
                'official_name' => 'Historic Monuments of Ancient Nara',
                'name' => 'Historic Monuments of Ancient Nara',
                'name_jp' => '古都奈良の文化財',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'state_parties' => ['JP'],
                'state_parties_meta' => [
                    'JP' => ['is_primary' => true, 'inscription_year' => 1998],
                ],
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1998,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6851,
                'longitude' => 135.8048,
                'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            ],
            [
                'id' => 2,
                'unesco_id' => '1234',
                'official_name' => 'Example Heritage Site',
                'name' => 'Example Heritage Site',
                'name_jp' => '例の文化遺産',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'state_parties' => ['JP'],
                'state_parties_meta' => [
                    'JP' => ['is_primary' => true, 'inscription_year' => 2000],
                ],
                'category' => 'natural',
                'criteria' => ['vii', 'viii'],
                'year_inscribed' => 2000,
                'area_hectares' => 500.0,
                'buffer_zone_hectares' => 400.0,
                'is_endangered' => true,
                'latitude' => 35.6895,
                'longitude' => 139.6917,
                'short_description' => 'An example of a natural heritage site.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1234/',
            ],
            [
                'id' => 3,
                'unesco_id' => '669',
                'official_name' => 'Shrines and Temples of Nikko',
                'name' => 'Shrines and Temples of Nikko',
                'name_jp' => '日光の社寺',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'state_parties' => ['JP'],
                'state_parties_meta' => [
                    'JP' => ['is_primary' => true, 'inscription_year' => 1999],
                ],
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1999,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 36.7578,
                'longitude' => 139.598,
                'short_description' => 'Lavishly decorated shrines set among ancient cedar trees.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/669/',
            ],
        ];
    }

    private static function arrayMultiData(): array
    {
        return [
            // 既存: ブナ林（ヨーロッパ各地）
            [
                'unesco_id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'name_jp' => null,
                'country' => 'Slovakia', // 便宜上代表国（実際は汎欧州の連続資産）
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
            ],

            // 追加: シルクロード（長安–天山回廊）
            [
                'unesco_id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk Roads: Chang'an–Tianshan Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China, Kazakhstan, Kyrgyzstan',
                'region' => 'Asia',
                'category' => 'cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 0.0,
                'buffer_zone_hectares' => 0.0,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational Silk Road corridor across China, Kazakhstan and Kyrgyzstan illustrating exchange of goods, ideas and beliefs.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['CN','KZ','KG'],
                'state_parties_meta' => [
                    'CN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KG' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }



    public function test_collection_check_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    $data['id'],
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['is_endangered'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null,
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, $this->arraySingleData())
        );

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $collection);
    }

    public function test_collection_check_empty_value(): void
    {
        $collection = new WorldHeritageEntityCollection();
        $this->assertSame([], $collection->getAllHeritages());
    }

    public function test_collection_check_count_value(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    $data['id'],
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['is_endangered'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null,
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, $this->arraySingleData())
        );

        $this->assertCount(3, $collection->getAllHeritages());
    }

    public function test_multi_collection_check_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    null, // IDは自動生成されるためnull
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['latitude'] ?? null,
                    $data['longitude'] ?? null,
                    $data['is_endangered'] ?? false,
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null,
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, self::arrayMultiData())
        );

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $collection);
    }

    public function test_multi_collection_check_count_value(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    null,
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['latitude'] ?? null,
                    $data['longitude'] ?? null,
                    $data['is_endangered'] ?? false,
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null,
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, self::arrayMultiData())
        );

        $this->assertCount(2, $collection->getAllHeritages());
    }
}