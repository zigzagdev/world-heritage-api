<?php

namespace App\Packages\Domains\Test;

use App\Models\Country;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
    }

    protected function tearDown(): void
    {
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
            'state_parties' => ['JP'],
            'state_parties_meta' => [
                'JP' => [
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


    public function test_entity_check_single_type(): void
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

        $this->assertInstanceOf(WorldHeritageEntity::class, $entity);
    }

    public function test_entity_check_single_value(): void
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

        $this->assertEquals(self::arraySingleData()['id'], $entity->getId());
        $this->assertEquals(self::arraySingleData()['official_name'], $entity->getOfficialName());
        $this->assertEquals(self::arraySingleData()['name'], $entity->getName());
        $this->assertEquals(self::arraySingleData()['country'], $entity->getCountry());
        $this->assertEquals(self::arraySingleData()['region'], $entity->getRegion());
        $this->assertEquals(self::arraySingleData()['category'], $entity->getCategory());
        $this->assertEquals(self::arraySingleData()['year_inscribed'], $entity->getYearInscribed());
        $this->assertEquals(self::arraySingleData()['latitude'], $entity->getLatitude());
        $this->assertEquals(self::arraySingleData()['longitude'], $entity->getLongitude());
        $this->assertEquals(self::arraySingleData()['is_endangered'], $entity->isEndangered());
        $this->assertEquals(self::arraySingleData()['name_jp'], $entity->getNameJp());
        $this->assertEquals(self::arraySingleData()['state_party'], $entity->getStateParty());
        $this->assertEquals(self::arraySingleData()['criteria'], $entity->getCriteria());
        $this->assertEquals(self::arraySingleData()['area_hectares'], $entity->getAreaHectares());
        $this->assertEquals(self::arraySingleData()['buffer_zone_hectares'], $entity->getBufferZoneHectares());
        $this->assertEquals(self::arraySingleData()['short_description'], $entity->getShortDescription());
        $this->assertEquals(self::arraySingleData()['image_url'], $entity->getImageUrl());
        $this->assertEquals(self::arraySingleData()['unesco_site_url'], $entity->getUnescoSiteUrl());
        $this->assertEquals(self::arraySingleData()['state_parties'], $entity->getStatePartyCodes());
        $this->assertEquals(self::arraySingleData()['state_parties_meta'], $entity->getStatePartyMeta());
    }

    public function test_entity_check_multi_type(): void
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

        $this->assertInstanceOf(WorldHeritageEntity::class, $entity);
    }

    public function test_entity_check_multi_value(): void
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

        $this->assertEquals(self::arrayMultiData()['id'], $entity->getId());
        $this->assertEquals(self::arrayMultiData()['official_name'], $entity->getOfficialName());
        $this->assertEquals(self::arrayMultiData()['name'], $entity->getName());
        $this->assertEquals(self::arrayMultiData()['country'], $entity->getCountry());
        $this->assertEquals(self::arrayMultiData()['region'], $entity->getRegion());
        $this->assertEquals(self::arrayMultiData()['category'], $entity->getCategory());
        $this->assertEquals(self::arrayMultiData()['year_inscribed'], $entity->getYearInscribed());
        $this->assertEquals(self::arrayMultiData()['is_endangered'], $entity->isEndangered());
        $this->assertEquals(self::arrayMultiData()['latitude'], $entity->getLatitude());
        $this->assertEquals(self::arrayMultiData()['longitude'], $entity->getLongitude());
        $this->assertEquals(self::arrayMultiData()['name_jp'], $entity->getNameJp());
        $this->assertEquals(self::arrayMultiData()['criteria'], $entity->getCriteria());
        $this->assertEquals(self::arrayMultiData()['area_hectares'], $entity->getAreaHectares());
        $this->assertEquals(self::arrayMultiData()['buffer_zone_hectares'], $entity->getBufferZoneHectares());
        $this->assertEquals(self::arrayMultiData()['short_description'], $entity->getShortDescription());
        $this->assertEquals(self::arrayMultiData()['image_url'], $entity->getImageUrl());
        $this->assertEquals(self::arrayMultiData()['unesco_site_url'], $entity->getUnescoSiteUrl());
        $this->assertEquals(self::arrayMultiData()['state_parties'], $entity->getStatePartyCodes());
        $this->assertEquals(self::arrayMultiData()['state_parties_meta'], $entity->getStatePartyMeta());
        $this->assertEquals(
            self::arrayMultiData()['state_parties_meta']['SK'],
            $entity->getStatePartyMeta()['SK']
        );
    }
}