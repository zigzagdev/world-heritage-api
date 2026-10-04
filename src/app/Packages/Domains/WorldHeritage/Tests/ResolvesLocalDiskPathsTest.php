<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use App\Console\Concerns\ResolvesLocalDiskPaths;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResolvesLocalDiskPathsTest extends TestCase
{
    private function host(): object
    {
        return new class {
            use ResolvesLocalDiskPaths;

            public function normalize(string $path): string
            {
                return $this->normalizeLocalDiskPath($path);
            }

            public function toDir(string $path): string
            {
                return $this->resolvePathToDir($path);
            }

            public function toFile(string $path): string
            {
                return $this->resolvePathToFile($path);
            }
        };
    }

    public function test_normalize_returns_empty_string_for_empty_input(): void
    {
        $this->assertSame('', $this->host()->normalize('   '));
    }

    public function test_normalize_strips_leading_slash(): void
    {
        $this->assertSame('unesco/foo.json', $this->host()->normalize('/unesco/foo.json'));
    }

    public function test_normalize_strips_storage_app_and_private_prefixes(): void
    {
        $this->assertSame(
            'unesco/foo.json',
            $this->host()->normalize('storage/app/private/unesco/foo.json'),
        );
    }

    public function test_resolve_path_to_file_returns_empty_string_for_empty_input(): void
    {
        $this->assertSame('', $this->host()->toFile('  '));
    }

    public function test_resolve_path_to_file_passes_through_absolute_unix_path(): void
    {
        $this->assertSame('/tmp/foo.json', $this->host()->toFile('/tmp/foo.json'));
    }

    public function test_resolve_path_to_file_passes_through_windows_drive_path(): void
    {
        $this->assertSame('C:\\foo.json', $this->host()->toFile('C:\\foo.json'));
    }

    public function test_resolve_path_to_file_resolves_relative_path_via_local_disk(): void
    {
        $expected = Storage::disk('local')->path('unesco/foo.json');

        $this->assertSame($expected, $this->host()->toFile('unesco/foo.json'));
    }

    public function test_resolve_path_to_dir_returns_disk_root_for_empty_input(): void
    {
        $expected = Storage::disk('local')->path('');

        $this->assertSame($expected, $this->host()->toDir('  '));
    }

    public function test_resolve_path_to_dir_passes_through_absolute_path(): void
    {
        $this->assertSame('/tmp/out', $this->host()->toDir('/tmp/out'));
    }

    public function test_resolve_path_to_dir_resolves_relative_path_via_local_disk(): void
    {
        $expected = Storage::disk('local')->path('unesco/normalized');

        $this->assertSame($expected, $this->host()->toDir('unesco/normalized'));
    }
}
