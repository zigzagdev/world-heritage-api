<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;



class GetWorldHeritageByIdTest extends TestCase
{
    private int $id;

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

        $this->id = $this->arrayData()['id'];

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
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'heritage_name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
            'country' => 'Slovakia',
            'country_name_jp' => 'スロバキア',
            'region' => 'Europe',
            'category' => 'Natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'year_inscribed' => 2007,
            'area_hectares' => 99_947.81,
            'buffer_zone_hectares' => 296_275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => '氷期後のブナの自然拡散史を示すヨーロッパ各地の原生的ブナ林群から成る越境・連続資産。',
            'short_description_jp' => 'あいうえお',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1133',
            'state_parties_codes' => [
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

    public function test_feature_test_ok(): void
    {
        $expectedCodes = [
            'ALB','AUT','BEL','BGR','BIH','CHE','CZE','DEU','ESP','FRA',
            'HRV','ITA','MKD','POL','ROU','SVK','SVN','UKR',
        ];

        $expected = [
            'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
            'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
            'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
            'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
            'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
            'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
            'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
            'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
            'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
            'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
            'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
            'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
            'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
            'POL' => ['is_primary' => false, 'inscription_year' => 2021],
            'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
            'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
            'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
            'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
        ];

        $orderedExpected = [];
        foreach ($expectedCodes as $code) {
            $orderedExpected[$code] = $expected[$code];
        }

        $res = $this->getJson("/api/v1/heritages/{$this->id}");

        $res->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'official_name',
                    'name',
                    'heritage_name_jp',
                    'country',
                    'country_name_jp',
                    'region',
                    'state_party',
                    'category',
                    'criteria',
                    'year_inscribed',
                    'area_hectares',
                    'buffer_zone_hectares',
                    'is_endangered',
                    'latitude',
                    'longitude',
                    'short_description',
                    'short_description_jp',
                    'images' => [
                        '*' => [
                            'id',
                            'url',
                            'sort_order',
                            'is_primary',
                        ]
                    ],
                    'unesco_site_url',
                    'state_party_codes',
                    'state_parties_meta',
                ]
            ]);

        $data = $res->json('data');

        $this->assertIsArray($data['images']);

        if (isset($data['images']) && $data['images'] !== []) {
            $this->assertArrayHasKey('id', $data['images'][0]);
            $this->assertArrayHasKey('url', $data['images'][0]);
            $this->assertArrayHasKey('sort_order', $data['images'][0]);
            $this->assertArrayHasKey('is_primary', $data['images'][0]);

            $orders = array_column($data['images'], 'sort_order');
            $sorted = $orders; sort($sorted);
            $this->assertSame($sorted, $orders);

            $this->assertTrue($data['images'][0]['is_primary']);
            if (count($data['images']) > 1) {
                $this->assertFalse($data['images'][1]['is_primary']);
            }
        }
    }

    public function test_ng_id_is_null(): void
    {
        $invalidId = 299;
        $this->getJson("/api/v1/heritages/{$invalidId}")
            ->assertStatus(404);
    }
}
