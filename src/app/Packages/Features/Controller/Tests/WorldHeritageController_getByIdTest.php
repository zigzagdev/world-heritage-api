<?php

namespace App\Packages\Features\Controller\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;
use Mockery;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageController_getByIdTest extends TestCase
{
    private $controller;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->controller = new WorldHeritageController();
        $seeder = new DatabaseSeeder();
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
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
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
            ]
        ];
    }

    private function mockDto(): WorldHeritageDto
    {
        $dto = Mockery::mock(WorldHeritageDto::class);

        $dto
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

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

        $dto
            ->shouldReceive('getStatePartyCodes')
            ->andReturn(self::arrayData()['state_parties']);

        $dto
            ->shouldReceive('getStatePartiesMeta')
            ->andReturn(self::arrayData()['state_parties_meta']);

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

    private function mockRequest(): Request
    {
        $request = Mockery::mock(Request::class);

        $request
            ->shouldReceive('route')
            ->with('id')
            ->andReturn(self::arrayData()['id']);

        return $request;
    }

    public function test_controller_work_valid(): void
    {
        $result = $this->controller->getWorldHeritageById(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }
}