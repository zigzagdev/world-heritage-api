<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use App\Models\WorldHeritage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportWorldHeritageSiteFromSplitFileIsTransboundaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->truncate();
    }

    protected function tearDown(): void
    {
        $this->truncate();
        parent::tearDown();
    }

    private function truncate(): void
    {
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
        WorldHeritage::truncate();
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function writeSplitFile(array $sites): string
    {
        Storage::fake('local');

        $path = 'unesco/normalized/test_world_heritage_sites.json';
        Storage::disk('local')->put($path, json_encode(['results' => $sites]));

        return $path;
    }

    public function test_import_persists_is_transboundary_true(): void
    {
        $path = $this->writeSplitFile([[
            'id' => 9001,
            'official_name' => 'Transboundary Test Site',
            'name' => 'Transboundary Test Site',
            'region' => 'EUR',
            'category' => 'Cultural',
            'criteria' => ['i'],
            'year_inscribed' => 2000,
            'is_transboundary' => true,
        ]]);

        Artisan::call('world-heritage:import-sites-split', ['--in' => $path]);

        $this->assertTrue(WorldHeritage::find(9001)->is_transboundary);
    }

    public function test_import_persists_is_transboundary_false_when_missing(): void
    {
        $path = $this->writeSplitFile([[
            'id' => 9002,
            'official_name' => 'Single Country Test Site',
            'name' => 'Single Country Test Site',
            'region' => 'EUR',
            'category' => 'Cultural',
            'criteria' => ['i'],
            'year_inscribed' => 2000,
        ]]);

        Artisan::call('world-heritage:import-sites-split', ['--in' => $path]);

        $this->assertFalse(WorldHeritage::find(9002)->is_transboundary);
    }
}
