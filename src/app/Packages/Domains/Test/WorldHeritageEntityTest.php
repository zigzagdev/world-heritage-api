<?php

namespace App\Packages\Domains\Test;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Models\WorldHeritageDescription;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

/**
 * @covers \App\Packages\Domains\WorldHeritageEntity
 */
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
            WorldHeritageDescription::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function arraySingleData(): array
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
            'state_party' => 'JPN',
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'short_description_jp' => 'これはテストです',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => null,
            'state_parties_meta' => [
                'JP' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
        ];
    }

    private function arrayMultiData(): array
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
            'area_hectares' => 99_947.81,
            'buffer_zone_hectares' => 296_275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
            'short_description_jp' => 'これはテストです',
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

    public function test_entity_check_single_type(): void
    {
        $entity = new WorldHeritageEntity(
            $this->arraySingleData()['id'],
            $this->arraySingleData()['official_name'],
            $this->arraySingleData()['name'],
            $this->arraySingleData()['country'],
            $this->arraySingleData()['region'],
            $this->arraySingleData()['category'],
            $this->arraySingleData()['year_inscribed'],
            $this->arraySingleData()['latitude'],
            $this->arraySingleData()['longitude'],
            $this->arraySingleData()['is_endangered'],
            $this->arraySingleData()['name_jp'],
            null, // countryNameJp
            $this->arraySingleData()['state_party'],
            $this->arraySingleData()['criteria'],
            $this->arraySingleData()['area_hectares'],
            $this->arraySingleData()['buffer_zone_hectares'],
            $this->arraySingleData()['short_description'],
            $this->arraySingleData()['short_description_jp'],
            null, // collection (ImageEntityCollection)
            $this->arraySingleData()['unesco_site_url'],
            $this->arraySingleData()['state_parties'] ?? [],
            $this->arraySingleData()['state_parties_meta'] ?? []
        );

        $this->assertInstanceOf(WorldHeritageEntity::class, $entity);
    }

    public function test_entity_check_single_value(): void
    {
        $entity = new WorldHeritageEntity(
            $this->arraySingleData()['id'],
            $this->arraySingleData()['official_name'],
            $this->arraySingleData()['name'],
            $this->arraySingleData()['country'],
            $this->arraySingleData()['region'],
            $this->arraySingleData()['category'],
            $this->arraySingleData()['year_inscribed'],
            $this->arraySingleData()['latitude'],
            $this->arraySingleData()['longitude'],
            $this->arraySingleData()['is_endangered'],
            $this->arraySingleData()['name_jp'],
            null,
            $this->arraySingleData()['state_party'],
            $this->arraySingleData()['criteria'],
            $this->arraySingleData()['area_hectares'],
            $this->arraySingleData()['buffer_zone_hectares'],
            $this->arraySingleData()['short_description'],
            $this->arraySingleData()['short_description_jp'],
            null,
            $this->arraySingleData()['unesco_site_url'],
            $this->arraySingleData()['state_parties'] ?: [],
            $this->arraySingleData()['state_parties_meta'] ?: []
        );

        $this->assertEquals($this->arraySingleData()['id'], $entity->getId());
        $this->assertEquals($this->arraySingleData()['official_name'], $entity->getOfficialName());
        $this->assertEquals($this->arraySingleData()['name'], $entity->getName());
        $this->assertEquals($this->arraySingleData()['country'], $entity->getCountry());
        $this->assertEquals($this->arraySingleData()['region'], $entity->getRegion());
        $this->assertEquals($this->arraySingleData()['category'], $entity->getCategory());
        $this->assertEquals($this->arraySingleData()['year_inscribed'], $entity->getYearInscribed());
        $this->assertEquals($this->arraySingleData()['latitude'], $entity->getLatitude());
        $this->assertEquals($this->arraySingleData()['longitude'], $entity->getLongitude());
        $this->assertEquals($this->arraySingleData()['is_endangered'], $entity->isEndangered());
        $this->assertEquals($this->arraySingleData()['state_party'], $entity->getStateParty());
        $this->assertEquals($this->arraySingleData()['criteria'], $entity->getCriteria());
        $this->assertEquals($this->arraySingleData()['area_hectares'], $entity->getAreaHectares());
        $this->assertEquals($this->arraySingleData()['buffer_zone_hectares'], $entity->getBufferZoneHectares());
        $this->assertEquals($this->arraySingleData()['short_description'], $entity->getShortDescription());
        $this->assertEquals($this->arraySingleData()['short_description_jp'], $entity->getShortDescriptionJp());
        $this->assertEquals($this->arraySingleData()['unesco_site_url'], $entity->getUnescoSiteUrl());
        $this->assertSame(['JPN'], $entity->getStatePartyCodes());
        $this->assertSame($this->arraySingleData()['state_parties_meta'], $entity->getStatePartyMeta());
    }

    public function test_entity_check_multi_type(): void
    {
        $entity = new WorldHeritageEntity(
            $this->arrayMultiData()['id'],
            $this->arrayMultiData()['official_name'],
            $this->arrayMultiData()['name'],
            $this->arrayMultiData()['country'],
            $this->arrayMultiData()['region'],
            $this->arrayMultiData()['category'],
            $this->arrayMultiData()['year_inscribed'],
            $this->arrayMultiData()['latitude'],
            $this->arrayMultiData()['longitude'],
            $this->arrayMultiData()['is_endangered'],
            $this->arrayMultiData()['name_jp'],
            null,
            $this->arrayMultiData()['state_party'],
            $this->arrayMultiData()['criteria'],
            $this->arrayMultiData()['area_hectares'],
            $this->arrayMultiData()['buffer_zone_hectares'],
            $this->arrayMultiData()['short_description'],
            $this->arrayMultiData()['short_description_jp'],
            null,
            $this->arrayMultiData()['unesco_site_url'],
            $this->arrayMultiData()['state_parties'] ?? [],
            $this->arrayMultiData()['state_parties_meta'] ?? []
        );

        $this->assertInstanceOf(WorldHeritageEntity::class, $entity);
    }

    public function test_entity_check_multi_value(): void
    {
        $entity = new WorldHeritageEntity(
            $this->arrayMultiData()['id'],
            $this->arrayMultiData()['official_name'],
            $this->arrayMultiData()['name'],
            $this->arrayMultiData()['country'],
            $this->arrayMultiData()['region'],
            $this->arrayMultiData()['category'],
            $this->arrayMultiData()['year_inscribed'],
            $this->arrayMultiData()['latitude'],
            $this->arrayMultiData()['longitude'],
            $this->arrayMultiData()['is_endangered'],
            $this->arrayMultiData()['name_jp'],
            null, // countryNameJp
            $this->arrayMultiData()['state_party'],
            $this->arrayMultiData()['criteria'],
            $this->arrayMultiData()['area_hectares'],
            $this->arrayMultiData()['buffer_zone_hectares'],
            $this->arrayMultiData()['short_description'],
            $this->arrayMultiData()['short_description_jp'],
            null, // collection
            $this->arrayMultiData()['unesco_site_url'],
            $this->arrayMultiData()['state_parties'] ?? [],
            $this->arrayMultiData()['state_parties_meta'] ?? []
        );

        $this->assertEquals($this->arrayMultiData()['id'], $entity->getId());
        $this->assertEquals($this->arrayMultiData()['official_name'], $entity->getOfficialName());
        $this->assertEquals($this->arrayMultiData()['name'], $entity->getName());
        $this->assertEquals($this->arrayMultiData()['country'], $entity->getCountry());
        $this->assertEquals($this->arrayMultiData()['region'], $entity->getRegion());
        $this->assertEquals($this->arrayMultiData()['category'], $entity->getCategory());
        $this->assertEquals($this->arrayMultiData()['year_inscribed'], $entity->getYearInscribed());
        $this->assertEquals($this->arrayMultiData()['is_endangered'], $entity->isEndangered());
        $this->assertEquals($this->arrayMultiData()['latitude'], $entity->getLatitude());
        $this->assertEquals($this->arrayMultiData()['longitude'], $entity->getLongitude());
        $this->assertEquals($this->arrayMultiData()['criteria'], $entity->getCriteria());
        $this->assertEquals($this->arrayMultiData()['area_hectares'], $entity->getAreaHectares());
        $this->assertEquals($this->arrayMultiData()['buffer_zone_hectares'], $entity->getBufferZoneHectares());
        $this->assertEquals($this->arrayMultiData()['short_description'], $entity->getShortDescription());
        $this->assertEquals($this->arrayMultiData()['short_description_jp'], $entity->getShortDescriptionJp());
        $this->assertEquals($this->arrayMultiData()['unesco_site_url'], $entity->getUnescoSiteUrl());
        $this->assertEquals($this->arrayMultiData()['state_parties'], $entity->getStatePartyCodes());
        $this->assertEquals($this->arrayMultiData()['state_parties_meta'], $entity->getStatePartyMeta());
        $this->assertEquals(
            $this->arrayMultiData()['state_parties_meta']['SVK'],
            $entity->getStatePartyMeta()['SVK']
        );
    }
}