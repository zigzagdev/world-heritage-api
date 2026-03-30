<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Image;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Enums\StudyRegion;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;

class WorldHeritageQueryService_countEachRegionTest extends TestCase
{
    private $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $this->app->bind(WorldHeritageSearchPort::class, static function () {
            return new class implements WorldHeritageSearchPort {
                public function search($query, int $currentPage, int $perPage): HeritageSearchResult {
                    return new HeritageSearchResult(ids: [], total: 0, currentPage: 1, perPage: $perPage, lastPage: 0);
                }
            };
        });

        $this->queryService = app(WorldHeritageQueryService::class);
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
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function baseRecord(array $override = []): array
    {
        $now = now();
        return array_merge([
            'official_name'        => 'Test Site',
            'name'                 => 'Test Site',
            'name_jp'              => 'テスト',
            'country'              => 'Test Country',
            'region'               => 'Test Region',
            'study_region'         => StudyRegion::UNKNOWN->value,
            'category'             => 'Cultural',
            'criteria'             => json_encode(['i']),
            'year_inscribed'       => 2000,
            'area_hectares'        => null,
            'buffer_zone_hectares' => null,
            'is_endangered'        => false,
            'latitude'             => null,
            'longitude'            => null,
            'short_description'    => '',
            'unesco_site_url'      => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $override);
    }

    public function test_count_each_region(): void
    {
        DB::table('world_heritage_sites')->insert([
            // Africa × 3
            // id:9  Simien National Park (Ethiopia, AFR)
            $this->baseRecord([
                'id'           => 9,
                'official_name'=> 'Simien National Park',
                'name'         => 'Simien National Park',
                'study_region' => StudyRegion::AFRICA->value,
                'category'     => 'Natural',
                'latitude'     => 13.183_333_333_3,
                'longitude'    => 38.066_666_666_7,
            ]),
            // id:25 Djoudj National Bird Sanctuary (Senegal, AFR)
            $this->baseRecord([
                'id'           => 25,
                'official_name'=> 'Djoudj National Bird Sanctuary',
                'name'         => 'Djoudj National Bird Sanctuary',
                'study_region' => StudyRegion::AFRICA->value,
                'category'     => 'Natural',
                'latitude'     => 16.414_602,
                'longitude'    => -16.237_906,
            ]),
            // id:26 Island of Gorée (Senegal, AFR)
            $this->baseRecord([
                'id'           => 26,
                'official_name'=> 'Island of Gorée',
                'name'         => 'Island of Gorée',
                'study_region' => StudyRegion::AFRICA->value,
                'latitude'     => 14.667_22,
                'longitude'    => -17.400_83,
            ]),

            // Europe × 3
            // id:3  Aachen Cathedral (Germany, EUR)
            $this->baseRecord([
                'id'           => 3,
                'official_name'=> 'Aachen Cathedral',
                'name'         => 'Aachen Cathedral',
                'study_region' => StudyRegion::EUROPE->value,
                'latitude'     => 50.774_746_853_7,
                'longitude'    => 6.083_919_968,
            ]),
            // id:29 Historic Centre of Kraków (Poland, EUR)
            $this->baseRecord([
                'id'           => 29,
                'official_name'=> 'Historic Centre of Kraków',
                'name'         => 'Historic Centre of Kraków',
                'study_region' => StudyRegion::EUROPE->value,
                'latitude'     => 50.061_388_888_9,
                'longitude'    => 19.937_222_222_2,
            ]),
            // id:30 Historic Centre of Warsaw (Poland, EUR)
            $this->baseRecord([
                'id'           => 30,
                'official_name'=> 'Historic Centre of Warsaw',
                'name'         => 'Historic Centre of Warsaw',
                'study_region' => StudyRegion::EUROPE->value,
                'latitude'     => 52.25,
                'longitude'    => 21.013,
            ]),

            // North America × 2
            // id:4  L'Anse aux Meadows (Canada, EUR/North America)
            $this->baseRecord([
                'id'           => 4,
                'official_name'=> "L'Anse aux Meadows National Historic Site",
                'name'         => "L'Anse aux Meadows",
                'study_region' => StudyRegion::NORTH_AMERICA->value,
                'latitude'     => 51.584_722_222_2,
                'longitude'    => -55.55,
            ]),
            // id:27 Mesa Verde National Park (USA, EUR/North America)
            $this->baseRecord([
                'id'           => 27,
                'official_name'=> 'Mesa Verde National Park',
                'name'         => 'Mesa Verde National Park',
                'study_region' => StudyRegion::NORTH_AMERICA->value,
                'latitude'     => 37.261_666_67,
                'longitude'    => -108.485_555_6,
            ]),

            // South America × 2
            // id:1  Galápagos Islands (Ecuador, LAC)
            $this->baseRecord([
                'id'           => 1,
                'official_name'=> 'Galápagos Islands',
                'name'         => 'Galápagos Islands',
                'study_region' => StudyRegion::SOUTH_AMERICA->value,
                'category'     => 'Natural',
                'latitude'     => -0.689_86,
                'longitude'    => -90.501_319,
            ]),
            // id:2  City of Quito (Ecuador, LAC)
            $this->baseRecord([
                'id'           => 2,
                'official_name'=> 'City of Quito',
                'name'         => 'City of Quito',
                'study_region' => StudyRegion::SOUTH_AMERICA->value,
                'latitude'     => -0.22,
                'longitude'    => -78.512_083_333_3,
            ]),

            // Oceania × 1 (JSONにないため仮データ)
            $this->baseRecord([
                'id'           => 9999,
                'official_name'=> 'Test Oceania Site',
                'name'         => 'Test Oceania Site',
                'study_region' => StudyRegion::OCEANIA->value,
                'latitude'     => -25.0,
                'longitude'    => 130.0,
            ]),

            // Asia × 1 (JSONにないため仮データ)
            $this->baseRecord([
                'id'           => 9998,
                'official_name'=> 'Test Asia Site',
                'name'         => 'Test Asia Site',
                'study_region' => StudyRegion::ASIA->value,
                'latitude'     => 35.0,
                'longitude'    => 135.0,
            ]),

            // Unknown × 2 → カウントに含まれない
            // id:20 Ancient City of Damascus (Syria, ARB)
            $this->baseRecord([
                'id'           => 20,
                'official_name'=> 'Ancient City of Damascus',
                'name'         => 'Ancient City of Damascus',
                'study_region' => StudyRegion::UNKNOWN->value,
                'latitude'     => 33.510_833_333_3,
                'longitude'    => 36.309_722_222_2,
            ]),
            // id:8  Ichkeul National Park (Tunisia, ARB)
            $this->baseRecord([
                'id'           => 8,
                'official_name'=> 'Ichkeul National Park',
                'name'         => 'Ichkeul National Park',
                'study_region' => StudyRegion::UNKNOWN->value,
                'category'     => 'Natural',
                'latitude'     => 37.163_61,
                'longitude'    => 9.674_72,
            ]),
        ]);

        $result = $this->queryService->getEachRegionsHeritagesCount();

        $this->assertSame(3, $result[StudyRegion::AFRICA->value]);
        $this->assertSame(2, $result[StudyRegion::ASIA->value]);
        $this->assertSame(3, $result[StudyRegion::EUROPE->value]);
        $this->assertSame(2, $result[StudyRegion::NORTH_AMERICA->value]);
        $this->assertSame(2, $result[StudyRegion::SOUTH_AMERICA->value]);
        $this->assertSame(1, $result[StudyRegion::OCEANIA->value]);
        $this->assertArrayNotHasKey(StudyRegion::UNKNOWN->value, $result);
    }
}