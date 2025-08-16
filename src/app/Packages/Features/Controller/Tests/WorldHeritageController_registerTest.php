<?php

namespace App\Packages\Features\Controller\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;
use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;

class WorldHeritageController_registerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new WorldHeritageController();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function requestData(): array
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

    private function mockUseCase(): CreateWorldHeritageUseCase
    {
        $mock = Mockery::mock(CreateWorldHeritageUseCase::class);

        $mock
            ->shouldReceive('handle')
            ->with($this->requestData())
            ->andReturn($this->mockDto());

        return $mock;
    }

    private function mockDto(): WorldHeritageDto
    {
        $mock = Mockery::mock(WorldHeritageDto::class);

        $mock
            ->shouldReceive('getId')
            ->andReturn($this->requestData()['id']);

        $mock
            ->shouldReceive('getUnescoId')
            ->andReturn($this->requestData()['unesco_id']);

        $mock
            ->shouldReceive('getOfficialName')
            ->andReturn($this->requestData()['official_name']);

        $mock
            ->shouldReceive('getName')
            ->andReturn($this->requestData()['name']);

        $mock
            ->shouldReceive('getNameJp')
            ->andReturn($this->requestData()['name_jp']);

        $mock
            ->shouldReceive('getCountry')
            ->andReturn($this->requestData()['country']);

        $mock
            ->shouldReceive('getRegion')
            ->andReturn($this->requestData()['region']);

        $mock
            ->shouldReceive('getStateParty')
            ->andReturn($this->requestData()['state_party']);

        $mock
            ->shouldReceive('getCategory')
            ->andReturn($this->requestData()['category']);

        $mock
            ->shouldReceive('getCriteria')
            ->andReturn($this->requestData()['criteria']);

        $mock
            ->shouldReceive('getYearInscribed')
            ->andReturn($this->requestData()['year_inscribed']);

        $mock
            ->shouldReceive('getAreaHectares')
            ->andReturn($this->requestData()['area_hectares']);

        $mock
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn($this->requestData()['buffer_zone_hectares']);

        $mock
            ->shouldReceive('isEndangered')
            ->andReturn($this->requestData()['is_endangered']);

        $mock
            ->shouldReceive('getLatitude')
            ->andReturn($this->requestData()['latitude']);

        $mock
            ->shouldReceive('getLongitude')
            ->andReturn($this->requestData()['longitude']);

        $mock
            ->shouldReceive('getShortDescription')
            ->andReturn($this->requestData()['short_description']);

        $mock
            ->shouldReceive('getImageUrl')
            ->andReturn($this->requestData()['image_url']);

        $mock
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn($this->requestData()['unesco_site_url']);

        return $mock;
    }

    private function mockRequest(): Request
    {
        $mock = Mockery::mock(Request::class);

        $mock
            ->shouldReceive('all')
            ->andReturn($this->requestData());

        return $mock;
    }

    public function test_controller_check_type(): void
    {
        $result = $this->controller->registerOneWorldHeritage(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function test_controller_check_value(): void
    {
        $result = $this->controller->registerOneWorldHeritage(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertEquals(201, $result->getStatusCode());
    }
}