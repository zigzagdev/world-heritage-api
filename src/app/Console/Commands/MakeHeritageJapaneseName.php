<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeHeritageJapaneseName extends Command
{
    protected $signature = 'world-heritage:make-manual-names-json
        {--in=storage/app/private/unesco/manual_input.txt : Input text file}
        {--out=storage/app/private/unesco/site_names_manual.json : Output JSON file}
        {--pretty : Pretty print JSON}';

    protected $description = 'Convert manual world heritage names text into JSON rows.';

    public function handle(): int
    {
        $in  = (string)$this->option('in');
        $out = (string)$this->option('out');
        $pretty = (bool)$this->option('pretty');

        $inPath = $this->resolvePath($in);
        if (!is_file($inPath)) {
            $this->error("Input not found: {$inPath}");
            return self::FAILURE;
        }

        $text = @file_get_contents($inPath);
        if (!is_string($text) || $text === '') {
            $this->error("Failed to read: {$inPath}");
            return self::FAILURE;
        }

        $rows = $this->convert($text);

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($rows, $flags);
        if ($json === false) {
            $this->error("Failed to encode json");
            return self::FAILURE;
        }

        // Storage::disk('local') は storage/app 配下
        Storage::disk('local')->put($this->toLocalDiskPath($out), $json);

        $this->info("Wrote: storage/app/" . $this->toLocalDiskPath($out) . " (count=" . count($rows) . ")");
        return self::SUCCESS;
    }

    private function convert(string $text): array
    {
        $lines = preg_split("/\r\n|\r|\n/", $text) ?: [];
        $country = null;
        $rows = [];

        foreach ($lines as $raw) {
            $line = trim((string)$raw);
            if ($line === '') continue;

            if (!$this->isSiteLine($line)) {
                $country = $line;
                continue;
            }

            [$name, $years] = $this->splitNameAndYears($line);

            $isJa = $this->containsJapanese($name);

            $rows[] = [
                'id_no' => null,
                'name_ja' => $isJa ? $name : '',
                'name_en' => $isJa ? '' : $name,
                'country' => $country,
                'years' => $years,
            ];
        }

        return $rows;
    }

    private function containsJapanese(string $s): bool
    {
        return preg_match('/[\p{Hiragana}\p{Katakana}\p{Han}]/u', $s) === 1;
    }

    private function splitNameAndYears(string $line): array
    {
        $line = trim($line);
        $line = preg_replace('/\x{3000}/u', ' ', $line) ?? $line;
        $line = preg_replace('/\s+/u', ' ', $line) ?? $line;

        $years = '';

        if (preg_match('/\s*[\(（]([^()（）]+)[\)）]\s*$/u', $line, $m) === 1) {
            $years = trim((string)$m[1]);
            $line = preg_replace('/\s*[\(（][^()（）]+[\)）]\s*$/u', '', $line) ?? $line;
            $line = trim($line);
        }

        return [$line, $years];
    }

    private function isSiteLine(string $line): bool
    {
        $line = trim($line);
        if ($line === '') return false;
        return preg_match('/[\(（]\s*\d{4}.*[\)）]\s*$/u', $line) === 1;
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;
        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;
        return base_path($path);
    }

    private function toLocalDiskPath(string $path): string
    {
        $trimPath = trim($path);
        if ($trimPath === '') return $trimPath;
        if (str_starts_with($trimPath, 'storage/app/')) {
            return substr($trimPath, strlen('storage/app/'));
        }
        return ltrim($trimPath, '/');
    }
}
