<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\UpdateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\ListQuery\UpdateWorldHeritageListQuery;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritageUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class UpdateWorldHeritageUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
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
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林。",
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
                'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
                'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
                'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
                'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
                'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
                'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
                'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
                'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
                'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
                'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
                'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
                'POL' => ['is_primary' => false, 'inscription_year' => 2021],
                'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
                'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
                'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
                'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
                'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
            ]
        ];
    }

    private function mockListQuery(): UpdateWorldHeritageListQuery
    {
        $factory = Mockery::mock(
            'alias' . UpdateWorldHeritageListQueryFactory::class
        );

        $mock = Mockery::mock(UpdateWorldHeritageListQuery::class);

        $factory
            ->shouldReceive('build')
            ->andReturn($mock);

        $mock
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $mock
            ->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $mock
            ->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $mock
            ->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

        $mock
            ->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $mock
            ->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $mock
            ->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $mock
            ->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $mock
            ->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $mock
            ->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $mock
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $mock
            ->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $mock
            ->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $mock
            ->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $mock
            ->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $mock
            ->shouldReceive('getImageUrl')
            ->andReturn(self::arrayData()['image_url']);

        $mock
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        $mock
            ->shouldReceive('getStatePartyCodes')
            ->andReturn(self::arrayData()['state_parties']);

        $mock
            ->shouldReceive('getStatePartiesMeta')
            ->andReturn(self::arrayData()['state_parties_meta']);

        return $mock;
    }

    private function mockEntity(): WorldHeritageEntity
    {
        $mock = Mockery::mock(WorldHeritageEntity::class);

        $mock
            ->shouldReceive('getId')
            ->andReturn(self::arrayData()['id']);

        $mock
            ->shouldReceive('getOfficialName')
            ->andReturn(self::arrayData()['official_name']);

        $mock
            ->shouldReceive('getName')
            ->andReturn(self::arrayData()['name']);

        $mock
            ->shouldReceive('getNameJp')
            ->andReturn(self::arrayData()['name_jp']);

        $mock
            ->shouldReceive('getCountry')
            ->andReturn(self::arrayData()['country']);

        $mock
            ->shouldReceive('getRegion')
            ->andReturn(self::arrayData()['region']);

        $mock
            ->shouldReceive('getCategory')
            ->andReturn(self::arrayData()['category']);

        $mock
            ->shouldReceive('getCriteria')
            ->andReturn(self::arrayData()['criteria']);

        $mock
            ->shouldReceive('getYearInscribed')
            ->andReturn(self::arrayData()['year_inscribed']);

        $mock
            ->shouldReceive('getAreaHectares')
            ->andReturn(self::arrayData()['area_hectares']);

        $mock
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn(self::arrayData()['buffer_zone_hectares']);

        $mock
            ->shouldReceive('isEndangered')
            ->andReturn(self::arrayData()['is_endangered']);

        $mock
            ->shouldReceive('getLatitude')
            ->andReturn(self::arrayData()['latitude']);

        $mock
            ->shouldReceive('getLongitude')
            ->andReturn(self::arrayData()['longitude']);

        $mock
            ->shouldReceive('getShortDescription')
            ->andReturn(self::arrayData()['short_description']);

        $mock
            ->shouldReceive('getImageUrl')
            ->andReturn(self::arrayData()['image_url']);

        $mock
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn(self::arrayData()['unesco_site_url']);

        $mock
            ->shouldReceive('getStatePartyCodes')
            ->andReturn(self::arrayData()['state_parties']);

        $mock
            ->shouldReceive('getStatePartyMeta')
            ->andReturn(self::arrayData()['state_parties_meta']);

        return $mock;
    }

    private function mockRepository()
    {
        $repository = Mockery::mock(WorldHeritageRepositoryInterface
        ::class);

        $repository
            ->shouldReceive('updateOneHeritage')
            ->with(Mockery::type(WorldHeritageEntity::class))
            ->andReturn(new WorldHeritageEntity(
                self::arrayData()['id'],
                self::arrayData()['official_name'],
                self::arrayData()['name'],
                self::arrayData()['country'],
                self::arrayData()['region'],
                self::arrayData()['category'],
                self::arrayData()['year_inscribed'],
                self::arrayData()['latitude'],
                self::arrayData()['longitude'],
                self::arrayData()['is_endangered'],
                self::arrayData()['name_jp'],
                self::arrayData()['state_party'],
                self::arrayData()['criteria'],
                self::arrayData()['area_hectares'],
                self::arrayData()['buffer_zone_hectares'],
                self::arrayData()['short_description'],
                self::arrayData()['image_url'],
                self::arrayData()['unesco_site_url'],
                self::arrayData()['state_parties'],
                self::arrayData()['state_parties_meta']
            ));

        return $repository;
    }

    private function mockRequest(): Request
    {
        $mock = Mockery::mock(Request::class);

        $mock
            ->shouldReceive('all')
            ->andReturn(self::arrayData());

        return $mock;
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new UpdateWorldHeritageUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(
            self::arrayData()['id'],
            $this->mockRequest()
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new UpdateWorldHeritageUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(
            self::arrayData()['id'],
            $this->mockRequest()
        );

        $this->assertSame(self::arrayData()['id'], $result->getId());
        $this->assertSame(self::arrayData()['official_name'], $result->getOfficialName());
        $this->assertSame(self::arrayData()['name'], $result->getName());
        $this->assertSame(self::arrayData()['name_jp'], $result->getNameJp());
        $this->assertSame(self::arrayData()['country'], $result->getCountry());
        $this->assertSame(self::arrayData()['region'], $result->getRegion());
        $this->assertSame(self::arrayData()['category'], $result->getCategory());
        $this->assertSame(self::arrayData()['criteria'], $result->getCriteria());
        $this->assertSame(self::arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertSame(self::arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertSame(self::arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertSame(self::arrayData()['is_endangered'], $result->isEndangered());
        $this->assertSame(self::arrayData()['latitude'], $result->getLatitude());
        $this->assertSame(self::arrayData()['longitude'], $result->getLongitude());
        $this->assertSame(self::arrayData()['short_description'], $result->getShortDescription());
        $this->assertSame(self::arrayData()['image_url'], $result->getImageUrl());
        $this->assertSame(self::arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertSame(self::arrayData()['state_parties'], $result->getStatePartyCodes());
        $this->assertSame(self::arrayData()['state_parties_meta'], $result->getStatePartiesMeta());
    }
}