<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Mockery;
use Tests\TestCase;

final class GetWorldHeritageByIdUseCaseTest extends TestCase
{
    private $queryService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->queryService = app(WorldHeritageQueryService::class);
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

    private function mockEntity(): WorldHeritageEntity
    {
        $entity = Mockery::mock(WorldHeritageEntity::class);

        $entity
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $entity
            ->shouldReceive('getUnescoId')
            ->andReturn(self::arrayData()['unesco_id']);

        $entity
            ->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $entity
            ->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $entity
            ->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

        $entity
            ->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $entity
            ->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $entity
            ->shouldReceive('getStateParty')
            ->andReturn(self::arrayData()['state_party']);

        $entity
            ->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $entity
            ->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $entity
            ->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $entity
            ->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $entity
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $entity
            ->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $entity
            ->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $entity
            ->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $entity
            ->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $entity
            ->shouldReceive('getImageUrl')
            ->andReturn(self::arrayData()['image_url']);

        $entity
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        return $entity;
    }

    private function mockDto(): WorldHeritageDto
    {
        $dto = Mockery::mock(WorldHeritageDto::class);

        $dto
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $dto
            ->shouldReceive('getUnescoId')
            ->andReturn(self::arrayData()['unesco_id']);

        $dto
            ->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $dto
            ->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $dto
            ->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

        $dto
            ->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $dto
            ->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $dto
            ->shouldReceive('getStateParty')
            ->andReturn(self::arrayData()['state_party']);

        $dto
            ->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $dto
            ->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $dto
            ->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $dto
            ->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $dto
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $dto
            ->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $dto
            ->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $dto
            ->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $dto
            ->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $dto
            ->shouldReceive('getImageUrl')
            ->andReturn(self::arrayData()['image_url']);

        $dto
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        return $dto;
    }

    public function test_use_case(): void
    {
        $useCase = new GetWorldHeritageByIdUseCase($this->queryService);

        $result = $useCase->handle(self::arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }
}