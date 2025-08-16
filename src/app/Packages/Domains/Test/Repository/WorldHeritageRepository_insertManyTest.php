<?php

namespace App\Packages\Domains\Test\Repository;

use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageRepository;

class WorldHeritageRepository_insertManyTest extends TestCase
{

    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function arrayData(): array
    {
        return [
            [
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
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
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
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
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
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
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
                'created_at' => now(), 'updated_at' => now(),
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
                    $d['unesco_site_url'] ?? null
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
                    $data['unesco_site_url']
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

                break;
            }
        }
    }
}