<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use Mockery;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageDtoTest extends TestCase
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
        return [
            'id' => 1,
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
        ];
    }

    public function test_dto_check_type(): void
    {
        $data = $this->arrayData();
        $dto = new WorldHeritageDto(
            $data['id'],
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
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $dto);
    }

    public function test_dto_properties(): void
    {
        $data = $this->arrayData();

        $dto = new WorldHeritageDto(
            $data['id'],
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
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null
        );


        $this->assertSame($data['id'], $dto->getId());
        $this->assertSame($data['unesco_id'], $dto->getUnescoId());
        $this->assertSame($data['official_name'], $dto->getOfficialName());
        $this->assertSame($data['name'], $dto->getName());
        $this->assertSame($data['country'], $dto->getCountry());
        $this->assertSame($data['region'], $dto->getRegion());
        $this->assertSame($data['category'], $dto->getCategory());
        $this->assertSame($data['year_inscribed'], $dto->getYearInscribed());
        $this->assertSame($data['latitude'], $dto->getLatitude());
        $this->assertSame($data['longitude'], $dto->getLongitude());
        $this->assertSame($data['is_endangered'], $dto->isEndangered());
        $this->assertSame($data['name_jp'], $dto->getNameJp());
        $this->assertSame($data['state_party'], $dto->getStateParty());
        $this->assertSame($data['criteria'], $dto->getCriteria());
        $this->assertSame($data['area_hectares'], $dto->getAreaHectares());
        $this->assertSame($data['buffer_zone_hectares'], $dto->getBufferZoneHectares());
        $this->assertSame($data['short_description'], $dto->getShortDescription());
        $this->assertSame($data['image_url'], $dto->getImageUrl());
        $this->assertSame($data['unesco_site_url'], $dto->getUnescoSiteUrl());
    }
}