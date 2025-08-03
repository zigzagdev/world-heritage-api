<?php

namespace App\Packages\Features\Controller\Tests;

use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;
use Mockery;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageController_getByIdTest extends TestCase
{
    private $controller;
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new WorldHeritageController();
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

    private function mockUseCase(): GetWorldHeritageByIdUseCase
    {
        $useCase = Mockery::mock(GetWorldHeritageByIdUseCase::class);

        $useCase
            ->shouldReceive('handle')
            ->with(self::arrayData()['id'])
            ->andReturn($this->mockDto());

        return $useCase;
    }

    public function test_controller_work_valid(): void
    {
        $result = $this->controller->getWorldHeritageById(
            self::arrayData()['id'],
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }
}