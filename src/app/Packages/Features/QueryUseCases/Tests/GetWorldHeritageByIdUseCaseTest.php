<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Mockery;
use Tests\TestCase;
use Database\Seeders\JapaneseWorldHeritageSeeder;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;

final class GetWorldHeritageByIdUseCaseTest extends TestCase
{
    private $queryService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->queryService = app(WorldHeritageQueryService::class);
        $seeder = new JapaneseWorldHeritageSeeder();
        $seeder->run();
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