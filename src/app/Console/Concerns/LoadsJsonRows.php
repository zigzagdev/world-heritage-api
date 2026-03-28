<?php

namespace App\Console\Concerns;

use Illuminate\Support\Facades\Storage;

trait LoadsJsonRows
{
    private function loadRows(string $path): ?array
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return null;
        }

        if (array_key_exists('results', $json)) {
            return is_array($json['results']) ? $json['results'] : null;
        }

        return array_is_list($json) ? $json : null;
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }

        if (str_starts_with($path, 'private/')) {
            $path = substr($path, strlen('private/'));
        }

        return Storage::disk('local')->path($path);
    }
}