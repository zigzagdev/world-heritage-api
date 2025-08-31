<?php

namespace App\Packages\Domains\Test\Repository;

use App\Models\Country;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepository;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DatabaseSeeder;
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
        $seeder = new CountrySeeder();
        $seeder->run();
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
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arraySingleData(): array
    {
        return [
            'id' => 668,
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'state_party' => null,
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => ['JPN'],
            'state_parties_meta' => [
                'JPN' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
        ];
    }

    private static function arrayMultiData(): array
    {
        return [
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
        ];
    }

    public function test_insert_check_single_type(): void
    {
        $entity = new WorldHeritageEntity(
            self::arraySingleData()['id'],
            self::arraySingleData()['official_name'],
            self::arraySingleData()['name'],
            self::arraySingleData()['country'],
            self::arraySingleData()['region'],
            self::arraySingleData()['category'],
            self::arraySingleData()['year_inscribed'],
            self::arraySingleData()['latitude'],
            self::arraySingleData()['longitude'],
            self::arraySingleData()['is_endangered'],
            self::arraySingleData()['name_jp'],
            self::arraySingleData()['state_party'],
            self::arraySingleData()['criteria'],
            self::arraySingleData()['area_hectares'],
            self::arraySingleData()['buffer_zone_hectares'],
            self::arraySingleData()['short_description'],
            self::arraySingleData()['image_url'],
            self::arraySingleData()['unesco_site_url'],
            self::arraySingleData()['state_parties'],
            self::arraySingleData()['state_parties_meta']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }

    public function test_insert_check_single_value(): void
    {
        $entity = new WorldHeritageEntity(
            self::arraySingleData()['id'],
            self::arraySingleData()['official_name'],
            self::arraySingleData()['name'],
            self::arraySingleData()['country'],
            self::arraySingleData()['region'],
            self::arraySingleData()['category'],
            self::arraySingleData()['year_inscribed'],
            self::arraySingleData()['latitude'],
            self::arraySingleData()['longitude'],
            self::arraySingleData()['is_endangered'],
            self::arraySingleData()['name_jp'],
            self::arraySingleData()['state_party'],
            self::arraySingleData()['criteria'],
            self::arraySingleData()['area_hectares'],
            self::arraySingleData()['buffer_zone_hectares'],
            self::arraySingleData()['short_description'],
            self::arraySingleData()['image_url'],
            self::arraySingleData()['unesco_site_url'],
            self::arraySingleData()['state_parties'],
            self::arraySingleData()['state_parties_meta']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertEquals(self::arraySingleData()['id'], $result->getId());
        $this->assertEquals(self::arraySingleData()['official_name'], $result->getOfficialName());
        $this->assertEquals(self::arraySingleData()['name'], $result->getName());
        $this->assertEquals(self::arraySingleData()['country'], $result->getCountry());
        $this->assertEquals(self::arraySingleData()['region'], $result->getRegion());
        $this->assertEquals(self::arraySingleData()['category'], $result->getCategory());
        $this->assertEquals(self::arraySingleData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals(self::arraySingleData()['latitude'], $result->getLatitude());
        $this->assertEquals(self::arraySingleData()['longitude'], $result->getLongitude());
        $this->assertEquals(self::arraySingleData()['is_endangered'], $result->isEndangered());
        $this->assertEquals(self::arraySingleData()['name_jp'], $result->getNameJp());
        $this->assertEquals(self::arraySingleData()['criteria'], $result->getCriteria());
        $this->assertEquals(self::arraySingleData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals(self::arraySingleData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals(self::arraySingleData()['short_description'], $result->getShortDescription());
        $this->assertEquals(self::arraySingleData()['image_url'], $result->getImageUrl());
        $this->assertEquals(self::arraySingleData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals(self::arraySingleData()['state_parties'], $result->getStatePartyCodes());
    }

    public function test_insert_check_multi_type(): void
    {
        $entity = new WorldHeritageEntity(
            self::arrayMultiData()['id'],
            self::arrayMultiData()['official_name'],
            self::arrayMultiData()['name'],
            self::arrayMultiData()['country'],
            self::arrayMultiData()['region'],
            self::arrayMultiData()['category'],
            self::arrayMultiData()['year_inscribed'],
            self::arrayMultiData()['is_endangered'],
            self::arrayMultiData()['latitude'],
            self::arrayMultiData()['longitude'],
            self::arrayMultiData()['name_jp'],
            self::arrayMultiData()['state_party'],
            self::arrayMultiData()['criteria'],
            self::arrayMultiData()['area_hectares'],
            self::arrayMultiData()['buffer_zone_hectares'],
            self::arrayMultiData()['short_description'],
            self::arrayMultiData()['image_url'],
            self::arrayMultiData()['unesco_site_url'],
            self::arrayMultiData()['state_parties'],
            self::arrayMultiData()['state_parties_meta']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }

    public function test_insert_check_multi_value(): void
    {
        $entity = new WorldHeritageEntity(
            self::arrayMultiData()['id'],
            self::arrayMultiData()['official_name'],
            self::arrayMultiData()['name'],
            self::arrayMultiData()['country'],
            self::arrayMultiData()['region'],
            self::arrayMultiData()['category'],
            self::arrayMultiData()['year_inscribed'],
            self::arrayMultiData()['is_endangered'],
            self::arrayMultiData()['latitude'],
            self::arrayMultiData()['longitude'],
            self::arrayMultiData()['name_jp'],
            self::arrayMultiData()['state_party'],
            self::arrayMultiData()['criteria'],
            self::arrayMultiData()['area_hectares'],
            self::arrayMultiData()['buffer_zone_hectares'],
            self::arrayMultiData()['short_description'],
            self::arrayMultiData()['image_url'],
            self::arrayMultiData()['unesco_site_url'],
            self::arrayMultiData()['state_parties'],
            self::arrayMultiData()['state_parties_meta']
        );

        $result = $this->repository->insertHeritage($entity);

        $this->assertEquals(self::arrayMultiData()['id'], $result->getId());
        $this->assertEquals(self::arrayMultiData()['official_name'], $result->getOfficialName());
        $this->assertEquals(self::arrayMultiData()['name'], $result->getName());
        $this->assertEquals(self::arrayMultiData()['country'], $result->getCountry());
        $this->assertEquals(self::arrayMultiData()['region'], $result->getRegion());
        $this->assertEquals(self::arrayMultiData()['category'], $result->getCategory());
        $this->assertEquals(self::arrayMultiData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals(self::arrayMultiData()['is_endangered'], $result->isEndangered());
        $this->assertEquals(self::arrayMultiData()['latitude'], $result->getLatitude());
        $this->assertEquals(self::arrayMultiData()['longitude'], $result->getLongitude());
        $this->assertEquals(self::arrayMultiData()['name_jp'], $result->getNameJp());
        $this->assertEquals(self::arrayMultiData()['criteria'], $result->getCriteria());
        $this->assertEquals(self::arrayMultiData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals(self::arrayMultiData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals(self::arrayMultiData()['short_description'], $result->getShortDescription());
        $this->assertEquals(self::arrayMultiData()['image_url'], $result->getImageUrl());
        $this->assertEquals(self::arrayMultiData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEqualsCanonicalizing(self::arrayMultiData()['state_parties'], $result->getStatePartyCodes());
        $this->assertEquals(self::arrayMultiData()['state_parties_meta'], $result->getStatePartyMeta());
        $this->assertEqualsCanonicalizing(
            self::arrayMultiData()['state_parties_meta']['SVK'],
            $entity->getStatePartyMeta()['SVK']
        );
    }
}