<?php

namespace App\Packages\Features\Tests;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Models\Country;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Validation\ValidationException;

class DeleteManyHeritagesTest extends TestCase
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
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_delete_many_heritages_ok(): void
    {
        $idsToDelete = [1133, 1442];

        $response = $this->deleteJson('/api/v1/heritages?ids='.implode(',', $idsToDelete));

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Heritages were deleted.',
                 ]);

        foreach ($idsToDelete as $id) {
            $this->assertDatabaseMissing('world_heritage_sites', [
                'id' => $id,
            ]);
            $this->assertDatabaseMissing('site_state_parties', [
                'world_heritage_site_id' => $id,
            ]);
        }
    }

    public function test_delete_many_heritages_ng_id(): void
    {
        $idsToDelete = ['abs', 1442];

        $response = $this->deleteJson('/api/v1/heritages?ids='.implode(',', $idsToDelete));

        $response->assertStatus(500);
        $this->assertDatabaseHas('world_heritage_sites', [
            'id' => 1442,
        ]);
        $this->assertDatabaseHas('site_state_parties', [
            'world_heritage_site_id' => 1442,
        ]);
    }
}