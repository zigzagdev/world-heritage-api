<?php

namespace App\Console\Concerns;

trait LoadsJsonRows
{
    use ResolvesLocalDiskPaths;

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
        return $this->resolvePathToFile($path);
    }
}