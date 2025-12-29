<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportSiteStatePartiesFromSplitFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-site-state-parties-split
        {--in=storage/app/private/unesco/normalized/site_state_parties.json : Input split JSON file path}
        {--batch=500 : Upsert batch size}
        {--max=0 : 0 means no limit}
        {--dry-run : No DB writes}
        {--strict : Fail if any required mapping is missing (and optionally FK missing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pivot rows from split site_state_parties.json into site_state_parties (upsert by composite PK)';

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
            if ($max > 0 && $imported >= $max) break;
            if (!is_array($row)) { $skipped++; continue; }

            $siteId = $row['world_heritage_site_id'] ?? null;
            $code = strtoupper(trim((string)($row['state_party_code'] ?? '')));

            if (!(is_int($siteId) || (is_string($siteId) && is_numeric($siteId)))) {
                $skipped++;
                if ($strict) {
                    $this->error("Strict: missing/invalid world_heritage_site_id: " . json_encode($row, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }
            $siteId = (int) $siteId;

            if ($code === '' || strlen($code) !== 3) {
                $skipped++;
                if ($strict) {
                    $this->error("Strict: missing/invalid state_party_code: " . json_encode($row, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }

            $isPrimary = (int) (($row['is_primary'] ?? 0) ? 1 : 0);
            $year = $this->toNullableInt($row['inscription_year'] ?? null);

            if ($strict) {
                $existsSite = DB::table('world_heritage_sites')->where('id', $siteId)->exists();
                $existsCountry = DB::table('countries')->where('state_party_code', $code)->exists();
                if (!$existsSite || !$existsCountry) {
                    $this->error("Strict: FK missing. site={$siteId} exists=" . ($existsSite ? 'yes' : 'no') . ", country={$code} exists=" . ($existsCountry ? 'yes' : 'no'));
                    return self::FAILURE;
                }
            }

            $batch[] = [
                'state_party_code' => $code,
                'world_heritage_site_id' => $siteId,
                'is_primary' => $isPrimary,
                'inscription_year' => $year,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            if (count($batch) >= $batchSize) {
                $imported += $this->flush($batch, $dryRun);
                $batch = [];
            }
        }

        if ($batch) {
            $imported += $this->flush($batch, $dryRun);
        }

        $this->info("site_state_parties upserted: {$imported}, skipped: {$skipped}" . ($dryRun ? " (dry-run)" : ""));
        return self::SUCCESS;
    }

    private function flush(array $rows, bool $dryRun): int
    {
        if ($dryRun) return count($rows);

        DB::table('site_state_parties')->upsert(
            $rows,
            ['state_party_code', 'world_heritage_site_id'],
            ['is_primary', 'inscription_year', 'updated_at']
        );

        return count($rows);
    }

    private function loadRows(string $path): ?array
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

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;
        return base_path($path);
    }

    private function toNullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '') return null;
        return is_numeric($v) ? (int) $v : null;
    }
}
