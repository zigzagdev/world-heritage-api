<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Console\Concerns\LoadsJsonRows;

class ImportCountriesFromSplitFile extends Command
{
    use LoadsJsonRows;

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

            if (!is_array($row)) {
                $skipped++;
                continue;
            }

            $code = strtoupper(trim((string) ($row['state_party_code'] ?? '')));
            if ($code === '' || strlen($code) !== 3) {
                $skipped++;
                if ($strict) {
                    $this->error('Strict: invalid state_party_code: ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return self::FAILURE;
                }
                continue;
            }

            $nameEn = $this->toNullableString($row['name_en'] ?? null) ?? $code;
            $nameJp = $this->toNullableString($row['name_jp'] ?? null) ?? $this->resolveCountryNameJapanese($code);
            $region = $this->toNullableString($row['region'] ?? null);

            if ($strict && $nameJp === null) {
                $this->error("Strict: name_jp could not be resolved for state_party_code [{$code}]");
                return self::FAILURE;
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

        DB::table('countries')->upsert($rows, ['state_party_code'], ['name_en', 'name_jp', 'region']);
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

    private function resolveCountryNameJapanese(string $iso3): ?string
    {
        $name = Config::get('country_ja.alpha3_to_country.' . strtoupper(trim($iso3)));
        return is_string($name) && $name !== '' ? $name : null;
    }
}