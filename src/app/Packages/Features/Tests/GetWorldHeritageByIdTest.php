<?php

namespace App\Packages\Features\Tests;

use Tests\TestCase;
use Database\Seeders\JapaneseWorldHeritageSeeder;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;

class GetWorldHeritageByIdTest extends TestCase
{
    private $id;
    protected function setUp(): void
    {
        parent::setUp();
        $this->id = 1;
        $this->refresh();
        $seeder = new JapaneseWorldHeritageSeeder();
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_feature_test_ok(): void
    {
        $result = $this->getJson("/api/v1/heritages/{$this->id}");

        $result->assertStatus(200);
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
                'unesco_site_url'
            ]
        ]);
    }

    public function test_ng_id_is_null(): void
    {
        $this->id = 299;

        $this->getJson("/api/v1/heritages/{$this->id}")
            ->assertStatus(404);
    }
}