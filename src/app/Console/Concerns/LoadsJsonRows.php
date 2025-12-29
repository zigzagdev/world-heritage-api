<?php

namespace App\Console\Concerns;

trait LoadsJsonRows
{
    protected function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;

        if (str_starts_with($path, '/')) return $path;

        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;

        return base_path($path);
    }

    protected function loadRows(string $path): ?array
    {
        $raw = @file_get_contents($path);
        if ($raw === false) return null;

        $json = json_decode($raw, true);
        if (!is_array($json)) return null;

        if (array_key_exists('results', $json)) {
            return is_array($json['results']) ? $json['results'] : null;
        }

        return array_is_list($json) ? $json : null;
    }
}
