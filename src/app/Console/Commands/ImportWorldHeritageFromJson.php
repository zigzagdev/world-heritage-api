<?php

namespace App\Console\Commands;

use App\Models\WorldHeritage;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportWorldHeritageFromJson extends Command
{
    protected $signature = 'world-heritage:import-json
        {--path=database/data/countries : File or directory (relative to project root)}
        {--max=0 : 0 means no limit}
        {--batch=200}';

    protected $description = 'Import world heritage sites from local JSON into DB (upsert, site only)';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        $max  = (int) $this->option('max');
        $batchSize = max(1, (int) $this->option('batch'));

        $fullPath = $this->resolvePath($path);

        if (!file_exists($fullPath)) {
            $this->error("Path not found: {$fullPath}");
            return self::FAILURE;
        }

        $files = $this->collectJsonFiles($fullPath);
        if ($files === []) {
            $this->error("No JSON files found: {$fullPath}");
            return self::FAILURE;
        }

        $imported = 0;
        $skipped  = 0;
        $batch = [];
        $now = Carbon::now();

        foreach ($files as $filePath) {
            if ($max > 0 && $imported >= $max) break;

            $results = $this->loadResultsFromJsonFile($filePath);
            if ($results === null) {
                $this->warn("Skipped invalid JSON: {$filePath}");
                continue;
            }

            foreach ($results as $row) {
                if ($max > 0 && $imported >= $max) break;
                if (!is_array($row)) { $skipped++; continue; }

                $mapped = $this->mapFromUnescoApiRow($row);

                if (empty($mapped['id'])) { $skipped++; continue; }

                $mapped['updated_at'] = $now;
                $mapped['created_at'] ??= $now;

                $batch[] = $mapped;

                if (count($batch) >= $batchSize) {
                    $imported += $this->flushBatch($batch);
                    $batch = [];
                }
            }
        }

        if ($batch) {
            $imported += $this->flushBatch($batch);
        }

        $this->info("Imported/updated {$imported} records. Skipped {$skipped} items.");
        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if ($path !== '' && ($path[0] === '/' || preg_match('/^[A-Za-z]:\\\\/', $path) === 1)) {
            return $path;
        }
        return base_path($path);
    }

    private function collectJsonFiles(string $fullPath): array
    {
        if (is_file($fullPath)) {
            return str_ends_with($fullPath, '.json') ? [$fullPath] : [];
        }

        $files = [];
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.json')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }

    private function loadResultsFromJsonFile(string $filePath): ?array
    {
        $raw = @file_get_contents($filePath);
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

        return $json;
    }

    private function mapFromUnescoApiRow(array $row): array
    {
        $id = $row['id_no'] ?? null;
        $lat = $row['coordinates']['lat'] ?? null;
        $lon = $row['coordinates']['lon'] ?? null;
        $criteriaRaw = $row['criteria_txt'] ?? $row['criteria'] ?? null;
        $stateParty = $row['states'] ?? $row['state_party'] ?? null;
        if (is_array($stateParty)) {
            $stateParty = $stateParty[0] ?? null;
        }

        $stateParty = is_string($stateParty) ? strtoupper(trim($stateParty)) : null;
        if ($stateParty === '') $stateParty = null;
        if ($stateParty !== null && !preg_match('/^[A-Z]{3}$/', $stateParty)) {
            $stateParty = null;
        }

        return [
            'id' => $this->toNullableInt($id),
            'official_name' => $row['official_name'] ?? null,
            'name' => $row['name_en'] ?? $row['name'] ?? null,
            'region' => $row['region_en'] ?? $row['region'] ?? null,
            'state_party' => $stateParty,
            'category' => $row['category'] ?? $row['type'] ?? null,
            'criteria' => $row['criteria'] ?? null,
            'year_inscribed' => $this->toNullableInt($row['date_inscribed'] ?? $row['year_inscribed'] ?? null),
            'area_hectares' => $this->toNullableFloat($row['area_hectares'] ?? null),
            'buffer_zone_hectares' => $this->toNullableFloat($row['buffer_zone_hectares'] ?? null),
            'is_endangered' => $this->toNullableBool($row['danger'] ?? $row['is_endangered'] ?? null),
            'latitude' => $this->toNullableFloat($lat),
            'longitude' => $this->toNullableFloat($lon),
            'short_description' => $row['short_description'] ?? $row['description'] ?? null,
            'image_url' => $row['image_url'] ?? $row['image'] ?? null,
            'thumbnail_image_id' => null,
            'unesco_site_url' => $row['url'] ?? null,
        ];
    }

    private function criteriaFromTxt(mixed $raw): array
    {
        if ($raw === null) return [];

        $s = trim((string) $raw);
        if ($s === '') return [];

        preg_match_all('/\(([^)]+)\)/', $s, $m);
        if (!empty($m[1])) {
            return array_values(array_filter(array_map(fn($v) => trim((string) $v), $m[1])));
        }

        $s = trim($s, " \t\n\r\0\x0B()");
        if ($s === '') return [];
        return [$s];
    }

    private function flushBatch(array $batch): int
    {
        $updateColumns = array_values(array_diff(array_keys($batch[0]), ['id']));

        dd([
            'row_image_url' => $row['image_url'] ?? null,
            'mapped_image_url' => $this->toNullableString($row['image_url'] ?? null)
        ]);

        WorldHeritage::query()->upsert(
            $batch,
            ['id'],
            $updateColumns
        );

        return count($batch);
    }

    private function extractIso3StateParty(array $row): ?string
    {
        $candidates = [];

        foreach (['primary_state_party_code', 'state_party_code', 'iso3', 'iso_code'] as $k) {
            if (!empty($row[$k]) && is_string($row[$k])) {
                $candidates[] = $row[$k];
            }
        }

        foreach (['state_party_codes', 'states_codes'] as $k) {
            if (!empty($row[$k]) && is_array($row[$k])) {
                $candidates[] = $row[$k][0] ?? null;
            }
        }

        $states = $row['states'] ?? $row['state_party'] ?? null;
        if (is_string($states)) $candidates[] = $states;
        if (is_array($states))  $candidates[] = $states[0] ?? null;

        foreach ($candidates as $c) {
            if (!is_string($c)) continue;
            $c = strtoupper(trim($c));
            if ($c !== '' && preg_match('/^[A-Z]{3}$/', $c)) {
                return $c;
            }
        }

        return null;
    }

    private function toNullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '') return null;
        return is_numeric($v) ? (int) $v : null;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;

        if (is_string($value)) {
            $value = str_replace(',', '', trim($value));
            if ($value === '') return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function toNullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') return null;

        if (is_bool($value)) return $value;

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }

        if (is_string($value)) {
            $v = strtolower(trim($value));
            if ($v === '') return null;

            $true  = ['1', 'true', 't', 'yes', 'y', 'on'];
            $false = ['0', 'false', 'f', 'no', 'n', 'off'];

            if (in_array($v, $true, true))  return true;
            if (in_array($v, $false, true)) return false;
        }

        return null;
    }
}
