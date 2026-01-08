<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeManualSiteNamesJson extends Command
{
    protected $signature = 'world-heritage:make-manual-names-json
        {--in=storage/app/private/unesco/manual_input.txt : Input text file}
        {--out=storage/app/private/unesco/site_names_manual.json : Output JSON file}
        {--pretty : Pretty print JSON}
        {--strict : Return FAILURE if any warnings are found}';

    protected $description = 'Convert manual world heritage names (Japanese/English mixed) text into JSON rows.';

    public function handle(): int
    {
        $in = (string) $this->option('in');
        $out = (string) $this->option('out');
        $pretty = (bool) $this->option('pretty');
        $strict = (bool) $this->option('strict');

        $inPath = $this->resolvePath($in);
        if (!is_file($inPath)) {
            $this->error("Input not found: {$inPath}");
            return self::FAILURE;
        }

        $text = @file_get_contents($inPath);
        if ($text === false || $text === '') {
            $detail = error_get_last();
            $reason = $detail['message'] ?? 'unknown reason';
            $this->error("Failed to read: {$inPath} ({$reason})");
            return self::FAILURE;
        }

        $result = $this->convert($text);

        if ($this->getOutput()->isVerbose() && !empty($result['country_logs'])) {
            foreach ($result['country_logs'] as $msg) {
                $this->line($msg);
            }
        }

        $errors = $result['errors'] ?? [];
        foreach ($errors as $e) {
            $this->error($e);
        }

        $warnings = $result['warnings'] ?? [];
        foreach ($warnings as $w) {
            $this->warn($w);
        }

        $stats = $result['stats'] ?? [];
        $this->info(sprintf(
            "Parsed: rows=%d, errors=%d, warnings=%d, countries_set=%d, site_without_country=%d",
            $stats['rows'] ?? 0,
            $stats['errors'] ?? 0,
            $stats['warnings'] ?? 0,
            $stats['countries_set'] ?? 0,
            $stats['site_without_country'] ?? 0
        ));

        if (!empty($errors)) {
            $this->error("Completed with errors. Output was not written.");
            return self::FAILURE;
        }

        if ($strict && !empty($warnings)) {
            $this->error("Completed with warnings under --strict. Output was not written.");
            return self::FAILURE;
        }

        $rows = $result['rows'] ?? [];

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($rows, $flags);
        if ($json === false) {
            $this->error("Failed to encode JSON");
            return self::FAILURE;
        }

        $outDiskPath = $this->toLocalDiskPath($out);
        if ($outDiskPath === '') {
            $this->error("Output path is empty.");
            return self::FAILURE;
        }

        Storage::disk('local')->put($outDiskPath, $json);
        $this->info("Wrote: storage/app/{$outDiskPath} (count=" . count($rows) . ")");

        return self::SUCCESS;
    }

    private function convert(string $text): array
    {
        $lines = preg_split("/\r\n|\r|\n/", $text) ?: [];
        $currentCountry = null;

        $rows = [];
        $errors = [];
        $warnings = [];
        $countryLogs = [];

        $countriesSet = 0;
        $siteWithoutCountry = 0;

        foreach ($lines as $i => $raw) {
            $lineNo = $i + 1;
            $line = trim((string) $raw);
            if ($line === '') continue;

            if (!$this->isSiteLine($line)) {
                if (!$this->isCountryLikeLine($line)) {
                    continue;
                }

                $currentCountry = $line;
                $countriesSet++;
                $countryLogs[] = "Country context set (line {$lineNo}): {$currentCountry}";
                continue;
            }

            if ($currentCountry === null) {
                $siteWithoutCountry++;
                $errors[] = "Site line found before any country context (line {$lineNo}): {$line}";
                continue;
            }

            [$name, $years] = $this->splitNameAndYears($line);
            $isJa = $this->containsJapanese($name);

            if ($name === '') {
                $warnings[] = "Empty site name after normalization (line {$lineNo})";
                continue;
            }

            $rows[] = [
                'id_no' => null,
                'name_ja' => $isJa ? $name : '',
                'name_en' => $isJa ? '' : $name,
                'country' => $currentCountry,
                'years' => $years,
            ];
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'warnings' => $warnings,
            'country_logs' => $countryLogs,
            'stats' => [
                'rows' => count($rows),
                'errors' => count($errors),
                'warnings' => count($warnings),
                'countries_set' => $countriesSet,
                'site_without_country' => $siteWithoutCountry,
            ],
        ];
    }

    private function isCountryLikeLine(string $line): bool
    {
        $line = trim($line);
        if ($line === '') return false;
        if (preg_match('/^(#|\/\/|;|※)/u', $line) === 1) return false;
        if (preg_match('/^[-=]{3,}$/u', $line) === 1) return false;
        if ($this->isSiteLine($line)) return false;

        return true;
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
            $years = trim((string) $m[1]);
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
