<?php

namespace App\Packages\Domains\Test\Repository;

use Database\Seeders\CountrySeeder;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageRepository;
use App\Models\Country;

class WorldHeritageRepository_insertManyTest extends TestCase
{

    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
        $seeder->run();
        $this->repository =  app(WorldHeritageRepository::class);
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

    private function arrayData(): array
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

    public function test_check_return_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($d) {
                return new WorldHeritageEntity(
                    null,
                    $d['unesco_id'],
                    $d['official_name'],
                    $d['name'],
                    $d['country'],
                    $d['region'],
                    $d['category'],
                    (int) $d['year_inscribed'],
                    isset($d['latitude']) ? (float) $d['latitude'] : null,
                    isset($d['longitude']) ? (float) $d['longitude'] : null,
                    (bool) ($d['is_endangered'] ?? false),
                    $d['name_jp'] ?? null,
                    $d['state_party'] ?? null,
                    is_string($d['criteria'] ?? null)
                        ? json_decode($d['criteria'], true, 512, JSON_THROW_ON_ERROR)
                        : ($d['criteria'] ?? []),
                    isset($d['area_hectares']) ? (float) $d['area_hectares'] : null,
                    isset($d['buffer_zone_hectares']) ? (float) $d['buffer_zone_hectares'] : null,
                    $d['short_description'] ?? null,
                    $d['image_url'] ?? null,
                    $d['unesco_site_url'] ?? null,
                    $d['state_parties'] ?? [],
                    $d['state_parties_meta'] ?? []
                );
            }, self::arrayData())
        );

        $result = $this->repository->insertHeritages($collection);
        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $result);
    }

    public function test_check_return_value(): void
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
                    $data['latitude'],
                    $data['longitude'],
                    $data['is_endangered'],
                    $data['name_jp'],
                    $data['state_party'],
                    $data['criteria'],
                    $data['area_hectares'],
                    $data['buffer_zone_hectares'],
                    $data['short_description'],
                    $data['image_url'],
                    $data['unesco_site_url'],
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, self::arrayData())
        );

        $result = $this->repository->insertHeritages($collection);

        foreach ($result->getAllHeritages() as $entity) {
            foreach (self::arrayData() as $value) {
                if ((string)$entity->getUnescoId() !== (string)$value['unesco_id']) {
                    continue;
                }
                $this->assertEquals($value['unesco_id'], $entity->getUnescoId());
                $this->assertEquals($value['official_name'], $entity->getOfficialName());
                $this->assertEquals($value['name'], $entity->getName());
                $this->assertEquals($value['country'], $entity->getCountry());
                $this->assertEquals($value['region'], $entity->getRegion());
                $this->assertEquals($value['category'], $entity->getCategory());
                $this->assertEquals($value['year_inscribed'], $entity->getYearInscribed());
                $this->assertEquals($value['latitude'], $entity->getLatitude());
                $this->assertEquals($value['longitude'], $entity->getLongitude());
                $this->assertEquals($value['is_endangered'], $entity->isEndangered());
                $this->assertEquals($value['name_jp'], $entity->getNameJp());
                $this->assertEquals($value['state_party'], $entity->getStateParty());
                $this->assertEquals($value['criteria'], $entity->getCriteria());
                $this->assertEquals($value['area_hectares'], $entity->getAreaHectares());
                $this->assertEquals($value['buffer_zone_hectares'], $entity->getBufferZoneHectares());
                $this->assertEquals($value['short_description'], $entity->getShortDescription());
                $this->assertEquals($value['image_url'], $entity->getImageUrl());
                $this->assertEquals($value['unesco_site_url'], $entity->getUnescoSiteUrl());
                $this->assertEquals($value['state_parties'], $entity->getStatePartyCodes());
                $this->assertEquals($value['state_parties_meta'], $entity->getStatePartyMeta());
                break;
            }
        }
    }
}