<?php

namespace App\Packages\Features\Tests;

use App\Models\Image;
use App\Packages\Domains\Infra\GcsImageObjectRemover;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\Country;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;
use App\Packages\Domains\Ports\ObjectRemovePort;

class DeleteOneHeritageTest extends TestCase
{
    private $endpoint = '/api/v1/heritages/{id}';
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();

        $this->app->bind(ObjectRemovePort::class, function () {
            return new class implements ObjectRemovePort {
                public function remove(string $disk, string $key): void {}
                public function removeByPrefix(string $disk, string $prefix): void {}
            };
        });

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

    public function test_delete_ok(): void
    {
        $result = $this->deleteJson(str_replace('{id}', '1418', $this->endpoint));

        $this->assertSame(200, $result->getStatusCode());

        $this->assertSoftDeleted('world_heritage_sites', [
            'id' => 1418,
        ]);

        $this->assertDatabaseMissing('site_state_parties', [
            'world_heritage_site_id' => 1418,
        ]);

        $this->assertSoftDeleted('images', [
            'world_heritage_id' => 1418,
        ]);
    }
}