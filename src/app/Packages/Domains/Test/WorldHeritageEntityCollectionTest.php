<?php

namespace App\Packages\Domains\Test;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityCollectionTest extends TestCase
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

    private static function arraySingleData(): array
    {
        return [
            [
                'id' => 668,
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
        ];
    }

    private static function arrayMultiData(): array
    {
        return [
            [
                'id' => 1133,
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
                ],
            ],
            [
                'id' => 1442,
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
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
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

        $this->assertCount(1, $collection->getAllHeritages());
    }

    public function test_multi_collection_check_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    $data['id'],
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
                    $data['id'],
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