<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Common\Pagination\PaginationDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdsUseCase;
use Mockery;
use Tests\TestCase;

class GetWorldHeritageByIdsUseCaseTest extends TestCase
{
    private int $currentPage;
    private int $perPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currentPage = 1;
        $this->perPage = 10;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
                ]
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
            ]
        ];
    }

    private function mockQueryService(): WorldHeritageQueryServiceInterface
    {
        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $queryService
            ->shouldReceive('getHeritagesByIds')
            ->with(
                Mockery::type('array'),
                $this->currentPage,
                $this->perPage
            )
            ->andReturn($this->mockPagination());

        return $queryService;
    }

    private function mockPagination(): PaginationDto
    {
        $pagination = Mockery::mock(PaginationDto::class);

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
            ->andReturn(self::arrayData());

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

    public function test_use_case_check_type(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $queryService = $this->mockQueryService();
        $useCase = new GetWorldHeritageByIdsUseCase($queryService);

        $result = $useCase->handle($ids, $this->currentPage, $this->perPage);

        $this->assertInstanceOf(PaginationDto::class, $result);
    }

    public function test_use_case_check_value_1(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $queryService = $this->mockQueryService();
        $useCase = new GetWorldHeritageByIdsUseCase($queryService);

        $result = $useCase->handle($ids, $this->currentPage, $this->perPage);

        $this->assertCount(2, $result->toArray()['data']);
        foreach ($result->toArray() as $key => $value) {
            if ($key === 'data') {
                foreach ($value as $k => $v) {
                    $this->assertSame(self::arrayData()[$k]['id'], $v['id']);
                    $this->assertSame(self::arrayData()[$k]['official_name'], $v['official_name']);
                    $this->assertSame(self::arrayData()[$k]['name'], $v['name']);
                    $this->assertSame(self::arrayData()[$k]['name_jp'], $v['name_jp']);
                    $this->assertSame(self::arrayData()[$k]['country'], $v['country']);
                    $this->assertSame(self::arrayData()[$k]['region'], $v['region']);
                    $this->assertSame(self::arrayData()[$k]['state_party'], $v['state_party']);
                    $this->assertSame(self::arrayData()[$k]['category'], $v['category']);
                    $this->assertSame(self::arrayData()[$k]['criteria'], $v['criteria']);
                    $this->assertSame(self::arrayData()[$k]['year_inscribed'], $v['year_inscribed']);
                    $this->assertSame(self::arrayData()[$k]['area_hectares'], $v['area_hectares']);
                    $this->assertSame(self::arrayData()[$k]['buffer_zone_hectares'], $v['buffer_zone_hectares']);
                    $this->assertSame(self::arrayData()[$k]['is_endangered'], $v['is_endangered']);
                    $this->assertSame(self::arrayData()[$k]['latitude'], $v['latitude']);
                    $this->assertSame(self::arrayData()[$k]['longitude'], $v['longitude']);
                    $this->assertSame(self::arrayData()[$k]['short_description'], $v['short_description']);
                    $this->assertSame(self::arrayData()[$k]['image_url'], $v['image_url']);
                    $this->assertSame(self::arrayData()[$k]['unesco_site_url'], $v['unesco_site_url']);
                }
            } else {
                $this->assertSame($result->toArray()[$key], $value);
            }
        }
    }

    public function test_use_case_check_value(): void
    {
        $ids = array_column(self::arrayData(), 'id');
        $queryService = $this->mockQueryService();
        $useCase = new GetWorldHeritageByIdsUseCase($queryService);

        $result = $useCase->handle($ids, $this->currentPage, $this->perPage);

        $data = $result->toArray()['data'];
        $this->assertCount(2, $data);

        foreach ($data as $row) {
            $this->assertArrayHasKey('image_url', $row);
            $this->assertIsString($row['image_url']);
            $this->assertMatchesRegularExpression('#^https?://#', $row['image_url']);
        }
    }
}