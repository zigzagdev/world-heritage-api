<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Tests\TestCase;

class GetWorldHeritageByIdDtoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayData(): array
    {
        return [
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

    public function test_dto_check_type(): void
    {
        $heritageDto = new WorldHeritageDto(
            id: self::arrayData()['id'],
            unescoId: self::arrayData()['unesco_id'],
            officialName: self::arrayData()['official_name'],
            name: self::arrayData()['name'],
            country: self::arrayData()['country'],
            region: self::arrayData()['region'],
            category: self::arrayData()['category'],
            yearInscribed: self::arrayData()['year_inscribed'],
            isEndangered: self::arrayData()['is_endangered'],
            latitude: self::arrayData()['latitude'],
            longitude: self::arrayData()['longitude'],
            nameJp: self::arrayData()['name_jp'],
            stateParty: self::arrayData()['state_party'],
            criteria: self::arrayData()['criteria'],
            areaHectares: self::arrayData()['area_hectares'],
            bufferZoneHectares: self::arrayData()['buffer_zone_hectares'],
            shortDescription: self::arrayData()['short_description'],
            imageUrl: self::arrayData()['image_url'],
            unescoSiteUrl: self::arrayData()['unesco_site_url']
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $heritageDto);
    }

    public function test_dto_check_value_type(): void
    {
        $heritageDto = new WorldHeritageDto(
            id: self::arrayData()['id'],
            unescoId: self::arrayData()['unesco_id'],
            officialName: self::arrayData()['official_name'],
            name: self::arrayData()['name'],
            country: self::arrayData()['country'],
            region: self::arrayData()['region'],
            category: self::arrayData()['category'],
            yearInscribed: self::arrayData()['year_inscribed'],
            isEndangered: self::arrayData()['is_endangered'],
            latitude: self::arrayData()['latitude'],
            longitude: self::arrayData()['longitude'],
            nameJp: self::arrayData()['name_jp'],
            stateParty: self::arrayData()['state_party'],
            criteria: self::arrayData()['criteria'],
            areaHectares: self::arrayData()['area_hectares'],
            bufferZoneHectares: self::arrayData()['buffer_zone_hectares'],
            shortDescription: self::arrayData()['short_description'],
            imageUrl: self::arrayData()['image_url'],
            unescoSiteUrl: self::arrayData()['unesco_site_url']
        );

        $this->assertIsInt($heritageDto->getId());
        $this->assertIsString($heritageDto->getUnescoId());
        $this->assertIsString($heritageDto->getOfficialName());
        $this->assertIsString($heritageDto->getName());
        $this->assertIsString($heritageDto->getCountry());
        $this->assertIsString($heritageDto->getRegion());
        $this->assertIsString($heritageDto->getCategory());
        $this->assertIsInt($heritageDto->getYearInscribed());
        $this->assertIsBool($heritageDto->isEndangered());
        $this->assertIsFloat($heritageDto->getLatitude());
        $this->assertIsFloat($heritageDto->getLongitude());
        $this->assertIsString($heritageDto->getNameJp());
        $this->assertIsString($heritageDto->getStateParty());
        $this->assertIsArray($heritageDto->getCriteria());
        $this->assertIsFloat($heritageDto->getAreaHectares());
        $this->assertIsFloat($heritageDto->getBufferZoneHectares());
        $this->assertIsString($heritageDto->getShortDescription());
        $this->assertIsString($heritageDto->getImageUrl());
        $this->assertIsString($heritageDto->getUnescoSiteUrl());
    }
}