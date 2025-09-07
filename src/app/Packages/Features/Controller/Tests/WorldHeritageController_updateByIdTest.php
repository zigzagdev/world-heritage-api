<?php

namespace App\Packages\Features\Controller\Tests;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Mockery;
use App\Packages\Features\Controller\WorldHeritageController;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritageUseCase;
use App\Models\WorldHeritage;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WorldHeritageController_updateByIdTest extends TestCase
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
        return [
            'id' => 1418,
            'official_name' => 'Fujisan, sacred place and source of artistic inspiration',
            'name' => 'Fujisan',
            'name_jp' => '富士山—信仰の対象と芸術の源泉(更新をした。)',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => null,
            'category' => 'Cultural',
            'criteria' => ['iii', 'vi'],
            'year_inscribed' => 2013,
            'area_hectares' => null,
            'buffer_zone_hectares' => null,
            'is_endangered' => false,
            'latitude' => null,
            'longitude' => null,
            'short_description' => '日本の象徴たる霊峰。信仰・芸術・登拝文化に深い影響を与えた文化的景観。',
            'image_url' => null,
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418',
            'state_parties' => ['FRA'],
            'state_parties_meta' => [
                'FRA' => [
                    'is_primary' => true,
                    'inscription_year' => 5000,
                ],
            ],
        ];
    }

    private function mockRequest(): Request
    {
        $mock = Mockery::mock(Request::class);

        $mock
            ->shouldReceive('all')
            ->andReturn(self::arrayData());

        return $mock;
    }

    private function mockUseCase(): UpdateWorldHeritageUseCase
    {
        $mock = Mockery::mock(UpdateWorldHeritageUseCase::class);

        $mock
            ->shouldReceive('handle')
            ->with(
                Mockery::type(Request::class),
            )
            ->andReturn(
                new WorldHeritageDto(
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
                    self::arrayData()['state_parties_meta'],
                )
            );

        return $mock;
    }

    public function test_check_controller_result_type(): void
    {
        $result =  $this->controller->updateOneWorldHeritage(
            $this->mockRequest(),
            $this->mockUseCase(),
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function test_check_controller_result_value(): void
    {
        $row = DB::table('world_heritage_sites')->where('id', 1418)->first();
        $oldNameJp = $row->name_jp;

        $result = $this->controller->updateOneWorldHeritage(
            $this->mockRequest(),
            $this->mockUseCase(),
        );
        $content = $result->getOriginalContent();

        $this->assertEquals(self::arrayData()['id'], $content['data']['id']);
        $this->assertEquals(self::arrayData()['official_name'], $content['data']['official_name']);
        $this->assertEquals(self::arrayData()['name'], $content['data']['name']);
        $this->assertEquals(self::arrayData()['name_jp'], $content['data']['name_jp']);
        $this->assertEquals(self::arrayData()['country'], $content['data']['country']);
        $this->assertEquals(self::arrayData()['region'], $content['data']['region']);
        $this->assertEquals(self::arrayData()['state_party'], $content['data']['state_party']);
        $this->assertEquals(self::arrayData()['category'], $content['data']['category']);
        $this->assertEquals(self::arrayData()['criteria'], $content['data']['criteria']);
        $this->assertEquals(self::arrayData()['year_inscribed'], $content['data']['year_inscribed']);
        $this->assertEquals(self::arrayData()['area_hectares'], $content['data']['area_hectares']);
        $this->assertEquals(self::arrayData()['buffer_zone_hectares'], $content['data']['buffer_zone_hectares']);
        $this->assertEquals(self::arrayData()['is_endangered'], $content['data']['is_endangered']);
        $this->assertEquals(self::arrayData()['latitude'], $content['data']['latitude']);
        $this->assertEquals(self::arrayData()['longitude'], $content['data']['longitude']);
        $this->assertEquals(self::arrayData()['short_description'], $content['data']['short_description']);
        $this->assertEquals(self::arrayData()['image_url'], $content['data']['image_url']);
        $this->assertEquals(self::arrayData()['unesco_site_url'], $content['data']['unesco_site_url']);
        foreach ($content['data']['state_party_codes'] as $key => $value) {
            $this->assertEquals(self::arrayData()['state_parties'][$key], $value);
        }
        foreach ($content['data']['state_parties_meta'] as $key => $value) {
            $this->assertEquals(self::arrayData()['state_parties_meta'][$key], $value);
        }
        $this->assertNotSame($oldNameJp, $content['data']['name_jp']);
    }
}