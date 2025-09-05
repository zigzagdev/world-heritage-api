<?php

namespace App\Packages\Features\Controller\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\Controller\WorldHeritageController;
use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;

class WorldHeritageController_getByIdsTest extends TestCase
{
    private $controller;
    private $currentPage;
    private $perPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->controller = new WorldHeritageController();
        $seeder = new DatabaseSeeder();
        $seeder->run();
        $this->currentPage = 1;
        $this->perPage = 10;
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
            [
                'id' => 1133,
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
                ],
            ],
            [
                'id' => 1442,
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
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    private static function dtoItems(): array
    {
        return array_map(
            fn(array $r) => new WorldHeritageDto(
                id: $r['id'],
                officialName: $r['official_name'],
                name: $r['name'],
                country: $r['country'],
                region: $r['region'],
                category: $r['category'],
                yearInscribed: $r['year_inscribed'],
                latitude: $r['latitude'],
                longitude: $r['longitude'],
                isEndangered: $r['is_endangered'],
                nameJp: $r['name_jp'],
                stateParty: $r['state_party'],
                criteria: $r['criteria'],
                areaHectares: $r['area_hectares'],
                bufferZoneHectares: $r['buffer_zone_hectares'],
                shortDescription: $r['short_description'],
                imageUrl: $r['image_url'],
                unescoSiteUrl: $r['unesco_site_url'],
                statePartyCodes: $r['state_parties'],
                statePartiesMeta: $r['state_parties_meta']
            ),
            self::arrayData()
        );
    }


    private function mockUseCase(): GetWorldHeritageByIdsUseCase
    {
        $mock = Mockery::mock(GetWorldHeritageByIdsUseCase::class);

        $mock->shouldReceive('handle')
            ->with(
                Mockery::type('array'),
                Mockery::type('int'),
                Mockery::type('int')
            )
            ->andReturn($this->mockPaginationDto());

        return $mock;
    }

    private function mockPaginationDto(): PaginationDto
    {
        $pagination = Mockery::mock(PaginationDto::class)->makePartial();

        $pagination
            ->shouldReceive('getPath')
            ->andReturn('http://example.com/api/heritages');

        $pagination
            ->shouldReceive('getCurrentPage')
            ->andReturn($this->currentPage);

        $pagination
            ->shouldReceive('getPerPage')
            ->andReturn($this->perPage);

        $pagination
            ->shouldReceive('getTotalItems')
            ->andReturn(count(self::arrayData()));

        $pagination
            ->shouldReceive('getTotalPages')
            ->andReturn(1);

        $pagination
            ->shouldReceive('getFrom')
            ->andReturn(1);

        $pagination
            ->shouldReceive('getTo')
            ->andReturn(count(self::arrayData()));

        $pagination
            ->shouldReceive('getItems')
            ->andReturn(self::arrayData());

        $pagination
            ->shouldReceive('getCollection')
            ->andReturn(self::dtoItems());

        $pagination
            ->shouldReceive('toArray')
            ->andReturn([
                'path'         => 'http://example.com/api/heritages',
                'current_page' => $this->currentPage,
                'per_page'     => $this->perPage,
                'total'        => count(self::arrayData()),
                'last_page'    => 1,
                'from'         => 1,
                'to'           => count(self::arrayData()),
                'data'         => self::arrayData(),
            ]);

        return $pagination;
    }

    public function test_controller_return_type(): void
    {
        $result = $this->controller->getWorldHeritagesByIds(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    private function mockRequest(): Request
    {
        $mock = Mockery::mock(Request::class);

        $mock->shouldReceive('get')
            ->with('ids', [])
            ->andReturn(implode(',', array_column(self::arrayData(), 'id')));

        $mock->shouldReceive('get')
            ->with('current_page', 1)
            ->andReturn($this->currentPage);

        $mock->shouldReceive('get')
            ->with('per_page', 30)
            ->andReturn($this->perPage);

        return $mock;
    }

    public function test_controller_return_value(): void
    {
        $result = $this->controller->getWorldHeritagesByIds(
            $this->mockRequest(),
            $this->mockUseCase()
        );

        $response = $result->getOriginalContent();

        $this->assertSame(count($response['data']['data']), count(self::arrayData()));

        foreach ($response['data']['data'] as $key => $value) {
            $this->assertSame($value['id'], self::arrayData()[$key]['id']);
            $this->assertSame($value['official_name'], self::arrayData()[$key]['official_name']);
            $this->assertSame($value['name'], self::arrayData()[$key]['name']);
            $this->assertSame($value['country'], self::arrayData()[$key]['country']);
            $this->assertSame($value['region'], self::arrayData()[$key]['region']);
            $this->assertSame($value['category'], self::arrayData()[$key]['category']);
            $this->assertSame($value['year_inscribed'], self::arrayData()[$key]['year_inscribed']);
            $this->assertSame($value['is_endangered'], self::arrayData()[$key]['is_endangered']);
            $this->assertSame($value['latitude'], self::arrayData()[$key]['latitude']);
            $this->assertSame($value['longitude'], self::arrayData()[$key]['longitude']);
            $this->assertSame($value['name_jp'], self::arrayData()[$key]['name_jp']);
            $this->assertSame($value['state_party'], self::arrayData()[$key]['state_party']);
            $this->assertSame($value['criteria'], self::arrayData()[$key]['criteria']);
            $this->assertSame($value['area_hectares'], self::arrayData()[$key]['area_hectares']);
            $this->assertSame($value['buffer_zone_hectares'], self::arrayData()[$key]['buffer_zone_hectares']);
            $this->assertSame($value['short_description'], self::arrayData()[$key]['short_description']);
            $this->assertSame($value['image_url'], self::arrayData()[$key]['image_url']);
            $this->assertSame($value['unesco_site_url'], self::arrayData()[$key]['unesco_site_url']);
            $this->assertSame($value['state_parties'], self::arrayData()[$key]['state_parties']);
            $this->assertSame($value['state_parties_meta'], self::arrayData()[$key]['state_parties_meta']);
        }
    }
}