<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MakeManualSiteNamesJsonTest extends TestCase
{
    private function writeInputFixture(): void
    {
        // Always written at the disk-relative key, regardless of which --in
        // string form (disk-relative or storage/app/private/...) a test uses,
        // since both forms must normalize to this same key.
        Storage::disk('local')->put('unesco/manual_input_test.txt', "Japan\nテストサイト (2000)\n");
    }

    public function test_accepts_disk_relative_input_path_like_other_commands(): void
    {
        Storage::fake('local');
        $this->writeInputFixture();

        $exitCode = Artisan::call('world-heritage:make-manual-names-json', [
            '--in' => 'unesco/manual_input_test.txt',
            '--out' => 'storage/app/private/unesco/manual_output_test.json',
        ]);

        $this->assertSame(0, $exitCode);
    }

    public function test_output_is_written_without_a_doubled_private_segment(): void
    {
        Storage::fake('local');
        $this->writeInputFixture();

        Artisan::call('world-heritage:make-manual-names-json', [
            '--in' => 'storage/app/private/unesco/manual_input_test.txt',
            '--out' => 'storage/app/private/unesco/manual_output_test.json',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('unesco/manual_output_test.json'));
        $this->assertFalse(Storage::disk('local')->exists('private/unesco/manual_output_test.json'));
    }
}
