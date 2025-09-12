<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritagesUseCase;
use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\ListQuery\UpdateWorldHeritageListQueryCollection;


class UpdateWorldHeritagesUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
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
            ],
        ];
    }

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface::class);

        $mock->shouldReceive('updateManyHeritages')
            ->with(Mockery::type(WorldHeritageEntityCollection::class))
            ->andReturnUsing(function (WorldHeritageEntityCollection $entities) {
                return $entities;
            });

        return $mock;
    }

    private function mockListQueryCollection(): UpdateWorldHeritageListQueryCollection
    {
        $mock = Mockery::mock(UpdateWorldHeritageListQueryCollection::class);

        $mock->shouldReceive('toArray')
            ->andReturn(self::arrayData());

        $items = array_map(
            static fn(array $row) => new WorldHeritageListQuery(
                id: $row['id'],
                official_name: (string)($row['official_name'] ?? ''),
                name: (string)($row['name'] ?? ''),
                country: (string)($row['country'] ?? ''),
                region: (string)($row['region'] ?? ''),
                category: (string)($row['category'] ?? ''),
                year_inscribed: (int)($row['year_inscribed'] ?? 0),
                latitude: isset($row['latitude'])  ? (float)$row['latitude']  : null,
                longitude: isset($row['longitude']) ? (float)$row['longitude'] : null,
                is_endangered: (bool)($row['is_endangered'] ?? false),
                name_jp: $row['name_jp'] ?? null,
                state_party: $row['state_party'] ?? null,
                criteria: is_string($row['criteria'] ?? null)
                    ? json_decode($row['criteria'], true)
                    : ($row['criteria'] ?? null),
                area_hectares: isset($row['area_hectares']) ? (float)$row['area_hectares'] : null,
                buffer_zone_hectares: isset($row['buffer_zone_hectares']) ? (float)$row['buffer_zone_hectares'] : null,
                short_description: $row['short_description'] ?? null,
                image_url: $row['image_url'] ?? null,
                unesco_site_url: $row['unesco_site_url'] ?? null,
                state_parties_codes: $row['state_parties'] ?? [],
                state_parties_meta: $row['state_parties_meta'] ?? []
            ),
            self::arrayData()
        );


        $mock
            ->shouldReceive('getItems')
            ->andReturn($items);

        return $mock;
    }

    public function test_use_case(): void
    {
        $useCase = new UpdateWorldHeritagesUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(
            $this->mockListQueryCollection()
        );

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);

        foreach ($result->toArray() as $key => $value) {
            $this->assertSame(self::arrayData()[$key]['id'], $value['id']);
            $this->assertSame(self::arrayData()[$key]['official_name'], $value['officialName']);
            $this->assertSame(self::arrayData()[$key]['name'], $value['name']);
            $this->assertSame(self::arrayData()[$key]['name_jp'], $value['nameJp']);
            $this->assertSame(self::arrayData()[$key]['country'], $value['country']);
            $this->assertSame(self::arrayData()[$key]['region'], $value['region']);
            $this->assertSame(self::arrayData()[$key]['category'], strtolower($value['category']));
            $this->assertSame(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertSame(self::arrayData()[$key]['state_party'], $value['stateParty']);
            $this->assertSame(self::arrayData()[$key]['year_inscribed'], $value['yearInscribed']);
            $this->assertSame(self::arrayData()[$key]['area_hectares'], $value['areaHectares']);
            $this->assertSame(self::arrayData()[$key]['buffer_zone_hectares'], $value['bufferZoneHectares']);
            $this->assertSame(self::arrayData()[$key]['is_endangered'], $value['isEndangered']);
            $this->assertSame(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertSame(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertSame(self::arrayData()[$key]['short_description'], $value['shortDescription']);
            $this->assertSame(self::arrayData()[$key]['image_url'], $value['imageUrl']);
            $this->assertSame(self::arrayData()[$key]['unesco_site_url'], $value['unescoSiteUrl']);
        }
    }
}