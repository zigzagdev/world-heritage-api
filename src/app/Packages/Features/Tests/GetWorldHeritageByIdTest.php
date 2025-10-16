<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\Ports\SignedUrlPort;
use Mockery;

class GetWorldHeritageByIdTest extends TestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

        $this->app->bind(SignedUrlPort::class, function () {
            $mock = Mockery::mock(SignedUrlPort::class);
            $mock->shouldReceive('forPut')->andReturnUsing(
                fn($disk, $key, $mime, $ttl = 600) => "https://example.test/put/{$disk}/{$key}?ttl={$ttl}"
            );
            $mock->shouldReceive('forGet')->andReturnUsing(
                fn($disk, $key, $ttl = 300) => "https://example.test/get/{$disk}/{$key}?ttl={$ttl}"
            );
            return $mock;
        });

        $this->id = self::arrayData()['id'];

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

    private static function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
            'country' => 'Slovakia',
            'region' => 'Europe',
            'category' => 'Natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'year_inscribed' => 2007,
            'area_hectares' => 99947.81,
            'buffer_zone_hectares' => 296275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => '氷期後のブナの自然拡散史を示すヨーロッパ各地の原生的ブナ林群から成る越境・連続資産。',
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
                    'name_jp',
                    'country',
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
                    'images' => [
                        '*' => [
                            'id',
                            'url',
                            'sort_order',
                            'width',
                            'height',
                            'format',
                            'alt',
                            'credit',
                            'is_primary',
                            'checksum',
                        ]
                    ],
                    'unesco_site_url',
                    'state_party_codes',
                    'state_parties_meta',
                ]
            ]);

        $data = $res->json('data');

        // 1) 配列で返る
        $this->assertIsArray($data['images']);

        // 画像があるときのみ、以下を検証
        if (!empty($data['images'])) {
            // 5) フィールド構造
            $this->assertArrayHasKey('id', $data['images'][0]);
            $this->assertArrayHasKey('url', $data['images'][0]);
            $this->assertArrayHasKey('sort_order', $data['images'][0]);
            $this->assertArrayHasKey('width', $data['images'][0]);
            $this->assertArrayHasKey('height', $data['images'][0]);
            $this->assertArrayHasKey('format', $data['images'][0]);
            $this->assertArrayHasKey('alt', $data['images'][0]);
            $this->assertArrayHasKey('credit', $data['images'][0]);
            $this->assertArrayHasKey('is_primary', $data['images'][0]);
            $this->assertArrayHasKey('checksum', $data['images'][0]);

            // 2) 並び順（sort_order昇順）
            $orders = array_column($data['images'], 'sort_order');
            $sorted = $orders; sort($sorted);
            $this->assertSame($sorted, $orders);

            // 3) primary 判定
            $this->assertTrue($data['images'][0]['is_primary']);
            if (count($data['images']) > 1) {
                $this->assertFalse($data['images'][1]['is_primary']);
            }

            // 4) 署名URL（SignedUrlPortをMockしてる前提）
            $this->assertStringStartsWith('https://example.test/get/', $data['images'][0]['url']);
            $this->assertStringContainsString('/seed/world_heritage/1133/img', $data['images'][0]['url']);
            $this->assertStringContainsString('ttl=300', $data['images'][0]['url']);

            // 6) 値の健全性
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $data['images'][0]['checksum']);
            $this->assertIsInt($data['images'][0]['width']);
            $this->assertIsInt($data['images'][0]['height']);
            $this->assertGreaterThan(0, $data['images'][0]['width']);
            $this->assertGreaterThan(0, $data['images'][0]['height']);
        }
    }

    public function test_ng_id_is_null(): void
    {
        $invalidId = 299;
        $this->getJson("/api/v1/heritages/{$invalidId}")
            ->assertStatus(404);
    }
}
