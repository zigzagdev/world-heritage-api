<?php

namespace App\Console\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesLocalDiskPaths
{
    private function normalizeLocalDiskPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }

        if (str_starts_with($path, 'private/')) {
            $path = substr($path, strlen('private/'));
        }

        return $path;
    }

    private function resolvePathToDir(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return Storage::disk('local')->path('');
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        $path = $this->normalizeLocalDiskPath($path);
        return Storage::disk('local')->path($path);
    }

    private function resolvePathToFile(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        $path = $this->normalizeLocalDiskPath($path);
        return Storage::disk('local')->path($path);
    }
}
