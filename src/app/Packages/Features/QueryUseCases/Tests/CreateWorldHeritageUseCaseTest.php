<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;

class CreateWorldHeritageUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
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

    private function arrayData(): array
    {
        return [
            'id' => 1,
            'unesco_id' => '668',
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'state_party' => null,
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => ['JP'],
            'state_parties_meta' => [
                'JP' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
        ];
    }

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface::class);

        $mock
            ->shouldReceive('insertHeritage')
            ->with(Mockery::type(WorldHeritageEntity::class))
            ->andReturn(
                new WorldHeritageEntity(
                    id: 1,
                    unescoId: $this->arrayData()['unesco_id'],
                    officialName: $this->arrayData()['official_name'],
                    name: $this->arrayData()['name'],
                    country: $this->arrayData()['country'],
                    region: $this->arrayData()['region'],
                    category: $this->arrayData()['category'],
                    yearInscribed: $this->arrayData()['year_inscribed'],
                    latitude: $this->arrayData()['latitude'],
                    longitude: $this->arrayData()['longitude'],
                    isEndangered: $this->arrayData()['is_endangered'],
                    nameJp: $this->arrayData()['name_jp'],
                    stateParty: $this->arrayData()['state_party'],
                    criteria: $this->arrayData()['criteria'],
                    areaHectares: $this->arrayData()['area_hectares'],
                    bufferZoneHectares: $this->arrayData()['buffer_zone_hectares'],
                    shortDescription: $this->arrayData()['short_description'],
                    imageUrl: $this->arrayData()['image_url'],
                    unescoSiteUrl: $this->arrayData()['unesco_site_url'],
                    statePartyCodes: $this->arrayData()['state_parties'],
                    statePartyMeta: $this->arrayData()['state_parties_meta'] ?? []
                )
            );

        return $mock;
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle($this->arrayData());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle($this->arrayData());

        $this->assertEquals(1, $result->getId());
        $this->assertEquals($this->arrayData()['unesco_id'], $result->getUnescoId());
        $this->assertEquals($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals($this->arrayData()['name'], $result->getName());
        $this->assertEquals($this->arrayData()['country'], $result->getCountry());
        $this->assertEquals($this->arrayData()['region'], $result->getRegion());
        $this->assertEquals($this->arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals($this->arrayData()['category'], $result->getCategory());
        $this->assertEquals($this->arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals($this->arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals($this->arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals($this->arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals($this->arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals($this->arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals($this->arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals($this->arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals($this->arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals($this->arrayData()['image_url'], $result->getImageUrl());
        $this->assertEquals($this->arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals($this->arrayData()['state_parties'], $result->getStatePartyCodes());
        $this->assertEquals($this->arrayData()['state_parties_meta'], $result->getStatePartiesMeta());
    }
}