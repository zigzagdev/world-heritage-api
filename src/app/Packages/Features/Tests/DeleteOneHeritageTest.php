<?php

namespace App\Packages\Features\Tests;

use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\Country;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;

class DeleteOneHeritageTest extends TestCase
{
    private $endpoint = '/api/v1/heritages/{id}';
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
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_delete_ok(): void
    {
        $result = $this->deleteJson(str_replace('{id}', '1418', $this->endpoint));

        $this->assertSame(200, $result->getStatusCode());
        $this->assertDatabaseMissing('world_heritage_sites', [
            'id' => 1418,
        ]);
        $this->assertDatabaseMissing('site_state_parties', [
            'world_heritage_site_id' => 1418,
        ]);
    }
}