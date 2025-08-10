<?php

namespace App\Packages\Domains\Test\Repository;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\WorldHeritage;

class WorldHeritageRepository_insertTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $this->repository = app(WorldHeritageRepository::class);
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

    private static function arrayData(): array
    {
        return [
            'unesco_id' => '668',
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
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
        ];
    }

    public function test_insert_check_type(): void
    {
        $entity = new WorldHeritageEntity(
            null,
            self::arrayData()['unesco_id'],
            self::arrayData()['official_name'],
            self::arrayData()['name'],
            self::arrayData()['country'],
            self::arrayData()['region'],
            self::arrayData()['category'],
            self::arrayData()['year_inscribed'],
            self::arrayData()['is_endangered'],
            self::arrayData()['latitude'],
            self::arrayData()['longitude'],
            self::arrayData()['name_jp'],
            self::arrayData()['state_party'],
            self::arrayData()['criteria'],
            self::arrayData()['area_hectares'],
            self::arrayData()['buffer_zone_hectares'],
            self::arrayData()['short_description'],
            self::arrayData()['image_url'],
            self::arrayData()['unesco_site_url']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }

    public function test_insert_check_value(): void
    {
        $entity = new WorldHeritageEntity(
            null,
            self::arrayData()['unesco_id'],
            self::arrayData()['official_name'],
            self::arrayData()['name'],
            self::arrayData()['country'],
            self::arrayData()['region'],
            self::arrayData()['category'],
            self::arrayData()['year_inscribed'],
            self::arrayData()['latitude'],
            self::arrayData()['longitude'],
            self::arrayData()['is_endangered'],
            self::arrayData()['name_jp'],
            self::arrayData()['state_party'],
            self::arrayData()['criteria'],
            self::arrayData()['area_hectares'],
            self::arrayData()['buffer_zone_hectares'],
            self::arrayData()['short_description'],
            self::arrayData()['image_url'],
            self::arrayData()['unesco_site_url']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertEquals(self::arrayData()['unesco_id'], $result->getUnescoId());
        $this->assertEquals(self::arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals(self::arrayData()['name'], $result->getName());
        $this->assertEquals(self::arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals(self::arrayData()['country'], $result->getCountry());
        $this->assertEquals(self::arrayData()['region'], $result->getRegion());
        $this->assertEquals(self::arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals(self::arrayData()['category'], $result->getCategory());
        $this->assertEquals(self::arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals(self::arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals(self::arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals(self::arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals(self::arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals(self::arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals(self::arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals(self::arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals(self::arrayData()['image_url'], $result->getImageUrl());
        $this->assertEquals(self::arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
    }
}