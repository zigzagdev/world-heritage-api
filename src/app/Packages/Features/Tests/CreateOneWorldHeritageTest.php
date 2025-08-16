<?php

namespace App\Packages\Features\Tests;

use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateOneWorldHeritageTest extends TestCase
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return [
            'unesco_id' => '660',
            'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
            'name' => 'Buddhist Monuments in the Horyu-ji Area',
            'name_jp' => '法隆寺地域の仏教建造物',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'year_inscribed' => 1993,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6147,
            'longitude' => 135.7355,
            'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
        ];
    }

    public function test_feature_check(): void
    {
        $result = $this->postJson('/api/v1/heritage', self::arrayData());

        $result->assertStatus(201);
        $result->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'unesco_id',
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
                'image_url',
                'unesco_site_url',
            ],
        ]);
    }
}