<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ImportWorldHeritageSiteCountryExceptionsFromSplitFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-site-country-exceptions
        {--in=unesco/normalized/exceptions-missing-iso-codes.json : Input exceptions JSON file path (local disk relative)}
        {--batch=200 : Upsert batch size}
        {--max=0 : 0 means no limit}
        {--dry-run : No DB writes}
        {--strict : Fail if FK site missing or required fields missing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import exception rows into world_heritage_site_country_exceptions (upsert by world_heritage_site_id + reason)';

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
        $now = Carbon::now();

        foreach ($rows as $row) {
            if ($max > 0 && $imported >= $max) {
                break;
            }
            if (!is_array($row)) { $skipped++; continue; }

            $idNo = $row['id_no']
                ?? $row['world_heritage_site_id']
                ?? $row['site_id']
                ?? null;

            if (!is_int($idNo) && !(is_string($idNo) && is_numeric($idNo))) {
                $skipped++;
                if ($strict) {
                    $this->error('Strict: missing/invalid id_no: ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }
            $siteId = (int) $idNo;

            $reason = trim((string) ($row['exception_type'] ?? $row['reason'] ?? ''));
            if ($reason === '') {
                $skipped++;
                if ($strict) {
                    $this->error('Strict: missing reason/exception_type: ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }

            if ($strict) {
                $existsSite = DB::table('world_heritage_sites')->where('id', $siteId)->exists();
                if (!$existsSite) {
                    $this->error("Strict: FK missing. world_heritage_sites.id={$siteId} not found");
                    return self::FAILURE;
                }
            }

            $batch[] = [
                'world_heritage_site_id' => $siteId,
                'reason' => $reason,
                'raw' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                $imported += $this->flush($batch, $dryRun);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $imported += $this->flush($batch, $dryRun);
        }

        $this->info("world_heritage_site_country_exceptions upserted: {$imported}, skipped: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function flush(array $rows, bool $dryRun): int
    {
        if ($dryRun) {
            return count($rows);
        }

        DB::table('world_heritage_site_country_exceptions')->upsert(
            $rows,
            ['world_heritage_site_id', 'reason'],
            ['raw', 'updated_at']
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
}