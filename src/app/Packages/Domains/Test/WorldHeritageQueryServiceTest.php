<?php

namespace App\Packages\Domains\Test;

use Tests\TestCase;
use Mockery;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageQueryServiceTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(WorldHeritageQueryService::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
        $heritage = Mockery::mock(WorldHeritageEntity::class);

        $heritage
            ->getId()
            ->shouldReturn($this->arrayData()['id']);

        return $heritage;
    }

    public function test_repository_check(): void
    {
        $result = $this->repository->getHeritageById($this->arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }
}