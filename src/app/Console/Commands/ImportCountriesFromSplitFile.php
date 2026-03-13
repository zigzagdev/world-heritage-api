<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportCountriesFromSplitFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-countries-split
        {--in=unesco/normalized/countries.json : Input split JSON file path (local disk relative)}
        {--batch=500 : Upsert batch size}
        {--max=0 : 0 means no limit}
        {--dry-run : No DB writes}
        {--strict : Fail if any required field is missing/invalid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import countries from split countries.json into countries table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $in = trim((string) $this->option('in'));
        $batchSize = max(1, (int) $this->option('batch'));
        $max = (int) $this->option('max');
        $dryRun = (bool) $this->option('dry-run');
        $strict = (bool) $this->option('strict');

        $path = $this->resolvePath($in);
        if (!is_file($path)) {
            $this->error("Input not found: {$path}");
            return self::FAILURE;
        }

        $rows = $this->loadRows($path);
        if ($rows === null) {
            $this->error("Invalid JSON shape: {$path} (expected {results:[...]} or [...])");
            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;
        $batch = [];

        foreach ($rows as $row) {
            if ($max > 0 && $imported >= $max) {
                break;
            }
            if (!is_array($row)) { $skipped++; continue; }

            $code = strtoupper(trim((string) ($row['state_party_code'] ?? '')));
            if ($code === '' || strlen($code) !== 3) {
                $skipped++;
                if ($strict) {
                    $this->error("Strict: invalid state_party_code: " . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }

            $nameEn = $this->toNullableString($row['name_en'] ?? null);
            $nameJp = $this->toNullableString($row['name_jp'] ?? null);
            $region = $this->toNullableString($row['region'] ?? null);

            if ($nameEn === null) {
                $nameEn = $code;
            }

            $batch[] = [
                'state_party_code' => $code,
                'name_en' => $nameEn,
                'name_jp' => $nameJp,
                'region' => $region,
            ];

            if (count($batch) >= $batchSize) {
                $imported += $this->flush($batch, $dryRun);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $imported += $this->flush($batch, $dryRun);
        }

        $this->info("countries upserted: {$imported}, skipped: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function flush(array $rows, bool $dryRun): int
    {
        if ($dryRun) {
            return count($rows);
        }

        DB::table('countries')->upsert(
            $rows,
            ['state_party_code'],
            ['name_en', 'name_jp', 'region']
        );

        return count($rows);
    }

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

    private function toNullableString(mixed $v): ?string
    {
        if (!is_string($v)) {
            return null;
        }

        $s = trim($v);
        return $s === '' ? null : $s;
    }
}