<?php

namespace App\Packages\Features\Tests;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetCountEachRegionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

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

        public function test_check_count_each_region_value(): void
        {
            $response = $this->getJson('/api/v1/heritages/region-count');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'region',
                            'count',
                        ],
                    ],
                ]);
        }

    public function test_returns_all_six_regions(): void
    {
        $response = $this->getJson('/api/v1/heritages/region-count');

        $data = $response->json('data');
        $regions = array_column($data, 'region');


        $this->assertCount(6, $data);
        $this->assertContains('Africa', $regions);
        $this->assertContains('Asia', $regions);
        $this->assertContains('Europe', $regions);
        $this->assertContains('North America', $regions);
        $this->assertContains('South America', $regions);
        $this->assertContains('Oceania', $regions);
    }

    public function test_count_is_positive_integer(): void
    {
        $response = $this->getJson('/api/v1/heritages/region-count');

        foreach ($response->json('data') as $item) {
            $this->assertIsInt($item['count']);
            $this->assertGreaterThanOrEqual(0, $item['count']);
        }
    }

    public function test_unknown_region_is_not_included(): void
    {
        $response = $this->getJson('/api/v1/heritages/region-count');

        $regions = array_column($response->json('data'), 'region');
        $this->assertNotContains('Unknown', $regions);
    }
}