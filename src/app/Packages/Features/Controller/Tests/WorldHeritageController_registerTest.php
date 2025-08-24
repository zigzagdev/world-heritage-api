<?php

namespace App\Packages\Features\Controller\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use Database\Seeders\CountrySeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;

class WorldHeritageController_registerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder =  new CountrySeeder();
        $seeder->run();
        $this->controller = new WorldHeritageController();
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

    private function requestData(): array
    {
        return [
            'id' => 1,
            'unesco_id' => '1133',
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
            'state_party_codes' => [
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

        $mock
            ->shouldReceive('getStatePartyCodes')
            ->andReturn($this->requestData()['state_party_codes']);

        $mock
            ->shouldReceive('getStatePartiesMeta')
            ->andReturn($this->requestData()['state_parties_meta'] ?? []);

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
        $this->assertEquals($this->requestData()['id'], $result->getOriginalContent()['data']['id']);
        $this->assertEquals($this->requestData()['unesco_id'], $result->getOriginalContent()['data']['unesco_id']);
        $this->assertEquals($this->requestData()['official_name'], $result->getOriginalContent()['data']['official_name']);
        $this->assertEquals($this->requestData()['name'], $result->getOriginalContent()['data']['name']);
        $this->assertEquals($this->requestData()['name_jp'], $result->getOriginalContent()['data']['name_jp']);
        $this->assertEquals($this->requestData()['country'], $result->getOriginalContent()['data']['country']);
        $this->assertEquals($this->requestData()['region'], $result->getOriginalContent()['data']['region']);
        $this->assertEquals($this->requestData()['category'], $result->getOriginalContent()['data']['category']);
        $this->assertEquals($this->requestData()['criteria'], $result->getOriginalContent()['data']['criteria']);
        $this->assertEquals($this->requestData()['year_inscribed'], $result->getOriginalContent()['data']['year_inscribed']);
        $this->assertEquals($this->requestData()['area_hectares'], $result->getOriginalContent()['data']['area_hectares']);
        $this->assertEquals($this->requestData()['buffer_zone_hectares'], $result->getOriginalContent()['data']['buffer_zone_hectares']);
        $this->assertEquals($this->requestData()['is_endangered'], $result->getOriginalContent()['data']['is_endangered']);
        $this->assertEquals($this->requestData()['latitude'], $result->getOriginalContent()['data']['latitude']);
        $this->assertEquals($this->requestData()['longitude'], $result->getOriginalContent()['data']['longitude']);
        $this->assertEquals($this->requestData()['short_description'], $result->getOriginalContent()['data']['short_description']);
        $this->assertEquals($this->requestData()['image_url'], $result->getOriginalContent()['data']['image_url']);
        $this->assertEquals($this->requestData()['unesco_site_url'], $result->getOriginalContent()['data']['unesco_site_url']);
        $this->assertEquals($this->requestData()['state_party_codes'], $result->getOriginalContent()['data']['state_party_codes']);
        $this->assertEquals($this->requestData()['state_parties_meta'], $result->getOriginalContent()['data']['state_parties_meta']);
    }
}