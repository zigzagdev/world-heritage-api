<?php

namespace App\Packages\Domains\Test;

use PHPUnit\Framework\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function arrayData(): array
    {
        return
            [
                'id' => 1,
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

    public function test_entity_check_type(): void
    {
        $heritageEntity = new WorldHeritageEntity(
            $this->arrayData()['id'],
            $this->arrayData()['unesco_id'],
            $this->arrayData()['official_name'],
            $this->arrayData()['name'],
            $this->arrayData()['country'],
            $this->arrayData()['region'],
            $this->arrayData()['category'],
            $this->arrayData()['year_inscribed'],
            $this->arrayData()['is_endangered'],
            $this->arrayData()['latitude'],
            $this->arrayData()['longitude'],
            $this->arrayData()['name_jp'],
            $this->arrayData()['state_party'],
            $this->arrayData()['criteria'],
            $this->arrayData()['area_hectares'],
            $this->arrayData()['buffer_zone_hectares'],
            $this->arrayData()['short_description'],
            $this->arrayData()['image_url'],
            $this->arrayData()['unesco_site_url']
        );

        $this->assertInstanceOf(WorldHeritageEntity::class, $heritageEntity);
    }

    public function test_entity_check_value(): void
    {
        $heritageEntity = new WorldHeritageEntity(
            $this->arrayData()['id'],
            $this->arrayData()['unesco_id'],
            $this->arrayData()['official_name'],
            $this->arrayData()['name'],
            $this->arrayData()['country'],
            $this->arrayData()['region'],
            $this->arrayData()['category'],
            $this->arrayData()['year_inscribed'],
            $this->arrayData()['latitude'],
            $this->arrayData()['longitude'],
            $this->arrayData()['is_endangered'],
            $this->arrayData()['name_jp'],
            $this->arrayData()['state_party'],
            $this->arrayData()['criteria'],
            $this->arrayData()['area_hectares'],
            $this->arrayData()['buffer_zone_hectares'],
            $this->arrayData()['short_description'],
            $this->arrayData()['image_url'],
            $this->arrayData()['unesco_site_url']
        );

        $this->assertSame($this->arrayData()['id'], $heritageEntity->getId());
        $this->assertSame($this->arrayData()['unesco_id'], $heritageEntity->getUnescoId());
        $this->assertSame($this->arrayData()['official_name'], $heritageEntity->getOfficialName());
        $this->assertSame($this->arrayData()['name'], $heritageEntity->getName());
        $this->assertSame($this->arrayData()['country'], $heritageEntity->getCountry());
        $this->assertSame($this->arrayData()['region'], $heritageEntity->getRegion());
        $this->assertSame($this->arrayData()['category'], $heritageEntity->getCategory());
        $this->assertSame($this->arrayData()['year_inscribed'], $heritageEntity->getYearInscribed());
        $this->assertSame($this->arrayData()['is_endangered'], $heritageEntity->isEndangered());
        $this->assertSame($this->arrayData()['latitude'], $heritageEntity->getLatitude());
        $this->assertSame($this->arrayData()['longitude'], $heritageEntity->getLongitude());
        $this->assertSame($this->arrayData()['name_jp'], $heritageEntity->getNameJp());
        $this->assertSame($this->arrayData()['state_party'], $heritageEntity->getStateParty());
        $this->assertSame($this->arrayData()['criteria'], $heritageEntity->getCriteria());
        $this->assertSame($this->arrayData()['area_hectares'], $heritageEntity->getAreaHectares());
        $this->assertSame($this->arrayData()['buffer_zone_hectares'], $heritageEntity->getBufferZoneHectares());
        $this->assertSame($this->arrayData()['short_description'], $heritageEntity->getShortDescription());
        $this->assertSame($this->arrayData()['image_url'], $heritageEntity->getImageUrl());
        $this->assertSame($this->arrayData()['unesco_site_url'], $heritageEntity->getUnescoSiteUrl());
    }
}