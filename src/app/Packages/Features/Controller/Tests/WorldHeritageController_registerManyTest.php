<?php

namespace App\Packages\Features\Controller\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Features\Controller\WorldHeritageController;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\WorldHeritageDtoCollectionFactory;
use Database\Seeders\CountrySeeder;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldManyHeritagesUseCase;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorldHeritageController_registerManyTest extends TestCase
{

    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
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

    private function mockDto(): WorldHeritageDtoCollection
    {
        $factory = Mockery::mock(
            'alias' . WorldHeritageDtoCollectionFactory::class
        );
        $mock = Mockery::mock(WorldHeritageDtoCollection::class);

        $collection = array_map(
            fn (array $data) => new WorldHeritageDto(
                $data['id'],
                $data['unesco_id'],
                $data['official_name'],
                $data['name'],
                $data['country'],
                $data['region'],
                $data['category'],
                $data['year_inscribed'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['is_endangered'] ?? false,
                $data['name_jp'] ?? null,
                $data['state_party'] ?? null,
                $data['criteria'] ?? [],
                $data['area_hectares'] ?? null,
                $data['buffer_zone_hectares'] ?? null,
                $data['short_description'] ?? null,
                $data['image_url'] ?? null,
                $data['unesco_site_url'] ?? null,
                $data['state_parties'] ?? [],
                $data['state_parties_meta'] ?? []

            ), self::arrayData()
        );

        $factory->shouldReceive('build')
            ->with(Mockery::type('array'))
            ->andReturn($mock);

        $mock->shouldReceive('getHeritages')
            ->andReturn($collection);

        return $mock;
    }

    private function mockUseCase(): CreateWorldManyHeritagesUseCase
    {
        $mock = Mockery::mock(CreateWorldManyHeritagesUseCase::class);

        $mock->shouldReceive('handle')
            ->with(Mockery::type('array'))
            ->andReturn($this->mockDto());

        return $mock;
    }

    private static function arrayData(): array
    {
        return [
            [
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
                'state_parties' => [
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
            ],
            [
                'id' => 2,
                'unesco_id' => '1442',
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk Roads: Chang'an–Tianshan Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China, Kazakhstan, Kyrgyzstan',
                'region' => 'Asia',
                'category' => 'cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 0.0,
                'buffer_zone_hectares' => 0.0,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational Silk Road corridor across China, Kazakhstan and Kyrgyzstan illustrating exchange of goods, ideas and beliefs.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['CN','KZ','KG'],
                'state_parties_meta' => [
                    'CN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KG' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    private function mockRequest(): Request
    {
        $request = Mockery::mock(Request::class);

        $request
            ->shouldReceive('all')
            ->andReturn(self::arrayData());

        return $request;
    }

    public function test_check_controller(): void
    {
        $result = $this->controller->registerManyWorldHeritages(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function test_check_controller_value(): void
    {
        $result = $this->controller->registerManyWorldHeritages(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $data = $result->getOriginalContent();

        foreach ($data['data'] as $key => $value) {
            $this->assertEquals(self::arrayData()[$key]['id'], $value['id']);
            $this->assertEquals(self::arrayData()[$key]['unesco_id'], $value['unesco_id']);
            $this->assertEquals(self::arrayData()[$key]['official_name'], $value['official_name']);
            $this->assertEquals(self::arrayData()[$key]['name'], $value['name']);
            $this->assertEquals(self::arrayData()[$key]['country'], $value['country']);
            $this->assertEquals(self::arrayData()[$key]['region'], $value['region']);
            $this->assertEquals(self::arrayData()[$key]['state_party'], $value['state_party']);
            $this->assertEquals(self::arrayData()[$key]['category'], $value['category']);
            $this->assertEquals(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertEquals(self::arrayData()[$key]['year_inscribed'], $value['year_inscribed']);
            $this->assertEquals(self::arrayData()[$key]['area_hectares'], $value['area_hectares']);
            $this->assertEquals(self::arrayData()[$key]['buffer_zone_hectares'], $value['buffer_zone_hectares']);
            $this->assertEquals(self::arrayData()[$key]['is_endangered'], $value['is_endangered']);
            $this->assertEquals(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertEquals(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value['short_description'] ?? null);
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value['image_url'] ?? null);
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value['unesco_site_url'] ?? null);
            $this->assertEquals(
                self::arrayData()[$key]['state_parties'],
                $value['state_party_codes']
            );
            $this->assertEquals(
                self::arrayData()[$key]['state_parties_meta'],
                $value['state_parties_meta']
            );
        }
    }
}