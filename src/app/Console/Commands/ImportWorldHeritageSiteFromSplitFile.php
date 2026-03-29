<?php

namespace App\Console\Commands;

use App\Console\Concerns\LoadsJsonRows;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWorldHeritageSiteFromSplitFile extends Command
{

    use LoadsJsonRows;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-sites-split
        {--in=unesco/normalized/world_heritage_sites.json : Input split JSON file path (local disk relative)}
        {--batch=200 : Upsert batch size}
        {--max=0 : 0 means no limit}
        {--dry-run : No DB writes}
        {--strict : Fail if any required field is missing/invalid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import world heritage sites from split world_heritage_sites.json into world_heritage_sites table (upsert by id)';

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
            if (!is_array($row)) {
                $skipped++;
                continue;
            }

            $id = $row['id'] ?? null;
            if (!is_int($id) && !(is_string($id) && is_numeric($id))) {
                $skipped++;
                if ($strict) {
                    $this->error("Strict: missing/invalid id: " . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }

            $batch[] = [
                'id' => (int) $id,
                'official_name' => $this->toNullableString($row['official_name'] ?? null),
                'name' => $this->toNullableString($row['name'] ?? null),
                'name_jp' => $this->toNullableString($row['name_jp'] ?? null),
                'study_region' => $this->toNullableString($row['study_region'] ?? null),
                'country' => $this->toNullableString($row['country'] ?? null),
                'region' => $this->toNullableString($row['region'] ?? null),
                'state_party' => $this->toNullableString($row['state_party'] ?? null),
                'category' => $this->toNullableString($row['category'] ?? null),
                'criteria' => isset($row['criteria']) ? json_encode($row['criteria'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : json_encode([]),
                'year_inscribed' => $this->toNullableInt($row['year_inscribed'] ?? null),
                'area_hectares' => $this->toNullableFloat($row['area_hectares'] ?? null),
                'buffer_zone_hectares' => $this->toNullableFloat($row['buffer_zone_hectares'] ?? null),
                'is_endangered' => $this->toNullableBoolInt($row['is_endangered'] ?? null),
                'latitude' => $this->toNullableFloat($row['latitude'] ?? null),
                'longitude' => $this->toNullableFloat($row['longitude'] ?? null),
                'short_description' => $this->toNullableString($row['short_description'] ?? null),
                'unesco_site_url' => $this->toNullableString($row['unesco_site_url'] ?? null),
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

        $this->info("world_heritage_sites upserted: {$imported}, skipped: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function flush(array $rows, bool $dryRun): int
    {
        if ($dryRun) {
            return count($rows);
        }

        DB::table('world_heritage_sites')->upsert(
            $rows,
            ['id'],
            array_values(array_diff(array_keys($rows[0]), ['id', 'created_at']))
        );
        return count($rows);
    }

    private function toNullableString(mixed $v): ?string
    {
        if (!is_string($v)) {
            return null;
        }
        $s = trim($v);
        return $s === '' ? null : $s;
    }

    private function toNullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (int) $v : null;
    }

    private function toNullableFloat(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_string($v)) {
            $v = str_replace(',', '', trim($v));
        }
        return is_numeric($v) ? (float) $v : null;
    }

    private function toNullableBoolInt(mixed $v): int
    {
        if ($v === null || $v === '') {
            return 0;
        }
        if (is_bool($v)) {
            return $v ? 1 : 0;
        }
        if (is_int($v) || is_float($v)) {
            return ((int) $v) === 1 ? 1 : 0;
        }
        if (is_string($v)) {
            $s = strtolower(trim($v));
            if (in_array($s, ['1', 'true', 't', 'yes', 'y', 'on'], true)) {
                return 1;
            }
        }
        return 0;
    }
}
