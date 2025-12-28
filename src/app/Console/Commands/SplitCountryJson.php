<?php

namespace App\Console\Commands;

use App\Support\CountryCodeNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class SplitCountryJson extends Command
{
    protected $signature = 'world-heritage:split-countries
        {--in= : Input UNESCO JSON file or directory (raw dump). Supports {"results":[...]} or [...] }
        {--out=unesco/normalized/countries.json : Output path in storage/app/... }
        {--pretty : Pretty print JSON}
        {--dry-run : Do not write output, only show counts}
        {--strict : Fail if any row cannot be mapped to at least one country code}
        {--merge-existing : Keep existing name_jp when countries.json already exists (recommended)}
        {--clean : If output exists, delete it before writing (name_jp merge will be skipped)}';

    protected $description = 'Split/normalize UNESCO JSON into countries.json for import (upsert-ready)';

    public function handle(): int
    {
        $in     = trim((string) $this->option('in'));
        $out    = trim((string) $this->option('out'));
        $pretty = (bool) $this->option('pretty');
        $dryRun = (bool) $this->option('dry-run');
        $strict = (bool) $this->option('strict');
        $mergeExisting = (bool) $this->option('merge-existing');
        $clean  = (bool) $this->option('clean');

        if ($in === '') {
            $this->error('Missing required option: --in');
            return self::FAILURE;
        }

        $inPath = $this->resolvePath($in);
        if (!file_exists($inPath)) {
            $this->error("Input not found: {$inPath}");
            return self::FAILURE;
        }

        $files = $this->collectJsonFiles($inPath);
        if ($files === []) {
            $this->error("No JSON files found in: {$inPath}");
            return self::FAILURE;
        }

        $outStoragePath = ltrim($out, '/');
        if ($clean && Storage::disk('local')->exists($outStoragePath) && !$dryRun) {
            Storage::disk('local')->delete($outStoragePath);
            $this->warn("Deleted existing output: storage/app/{$outStoragePath}");
        }

        // 既存countries.jsonのstate_party_code(=ISO3想定) をキーに name_jp を引く
        $existingJp = [];
        if ($mergeExisting && !$clean) {
            $existingJp = $this->readExistingCountriesJpMap($outStoragePath);
            if ($existingJp !== []) {
                $this->info('Loaded existing name_jp entries: ' . count($existingJp));
            }
        }

        /** @var CountryCodeNormalizer $normalizer */
        $normalizer = app(CountryCodeNormalizer::class);

        $countryMap = []; // key: ISO3
        $inputRows = 0;

        $invalidJsonFiles = 0;
        $rowsNotObject = 0;
        $rowsMissingCodes = 0;

        $rowsUnknownCodes = 0;
        $unknownSamples = []; // up to 10

        foreach ($files as $file) {
            $raw = @file_get_contents($file);
            if ($raw === false) {
                $this->warn("Skipped unreadable file: {$file}");
                $invalidJsonFiles++;
                continue;
            }

            $json = json_decode($raw, true);
            if (!is_array($json)) {
                $this->warn("Skipped invalid JSON: {$file}");
                $invalidJsonFiles++;
                continue;
            }

            $rows = $this->extractRows($json);
            if ($rows === null) {
                $this->warn("Skipped unknown JSON shape (expected {results:[...]} or [...]): {$file}");
                $invalidJsonFiles++;
                continue;
            }

            foreach ($rows as $row) {
                $inputRows++;

                if (!is_array($row)) {
                    $rowsNotObject++;
                    continue;
                }

                // ✅ states / iso_codes 両対応（どっちか入ってれば拾う）
                $codesRaw = $this->normalizeCodeList($row['states'] ?? $row['iso_codes'] ?? null);
                if ($codesRaw === []) {
                    $rowsMissingCodes++;
                    continue;
                }

                try {
                    // ✅ ISO3 に正規化（Normalizerは変更しない）
                    $codes = $normalizer->toIso3List($codesRaw);
                } catch (InvalidArgumentException $e) {
                    $rowsUnknownCodes++;

                    if (count($unknownSamples) < 10) {
                        $unknownSamples[] = [
                            'file' => $file,
                            'input_codes' => $codesRaw,
                            'message' => $e->getMessage(),
                        ];
                    }

                    if ($strict) {
                        $this->error('Strict mode: unknown country code detected.');
                        $this->line($e->getMessage());
                        return self::FAILURE;
                    }

                    continue;
                }

                if ($codes === []) {
                    $rowsMissingCodes++;
                    continue;
                }

                $names = $this->normalizeStringList($row['states_names'] ?? null);

                // ✅ region は region_code の 5値に寄せる（EUR/AFR/APA/ARB/LAC）
                $region = $this->normalizeRegionCode($row['region_code'] ?? null);

                // ✅ name_en は codes と names が同数の時だけ 1:1 で入れる（事故防止）
                if ($names !== [] && count($names) === count($codes)) {
                    foreach ($codes as $idx => $code) {
                        $en = trim((string)($names[$idx] ?? ''));
                        if ($en === '') $en = $code;

                        $this->upsertCountryRow(
                            countryMap: $countryMap,
                            code: $code,
                            nameEn: $en,
                            existingJp: $existingJp,
                            region: $region
                        );
                    }
                    continue;
                }

                // ✅ それ以外は安全側：name_en = code、region だけ埋める
                foreach ($codes as $code) {
                    $this->upsertCountryRow(
                        countryMap: $countryMap,
                        code: $code,
                        nameEn: null,
                        existingJp: $existingJp,
                        region: $region
                    );
                }
            }
        }

        ksort($countryMap, SORT_STRING);
        $results = array_values($countryMap);

        $this->line('----');
        $this->info('Input files: ' . count($files));
        $this->info("Input rows scanned: {$inputRows}");
        $this->info('Countries extracted (unique state_party_code): ' . count($results));
        $this->info("Invalid JSON files: {$invalidJsonFiles}");
        $this->info("Rows not object: {$rowsNotObject}");
        $this->info("Rows missing country codes: {$rowsMissingCodes}");
        $this->info("Rows unknown country codes: {$rowsUnknownCodes}");

        if ($unknownSamples !== []) {
            $this->warn('Unknown samples (up to 10):');
            foreach ($unknownSamples as $s) {
                $this->line('- ' . json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }

        if ($strict) {
            $fail = false;
            if ($invalidJsonFiles > 0) $fail = true;
            if ($rowsMissingCodes > 0) $fail = true;
            if ($rowsUnknownCodes > 0) $fail = true;

            if ($fail) {
                $this->error('Strict mode: invalid JSON files, rows missing codes, or unknown codes exist.');
                return self::FAILURE;
            }
        }

        $payload = [
            'meta' => [
                'schema' => 'countries.v1',
                'source' => 'unesco whc001',
                'country_code_standard' => 'alpha-3',
                'generated_at' => now()->toIso8601String(),
                'input' => $in,
                'input_files' => count($files),
                'input_rows_scanned' => $inputRows,
                'countries' => count($results),
                'merge_existing_name_jp' => $mergeExisting && !$clean,
                'region_standard' => 'region_code(EUR/AFR/APA/ARB/LAC)',
            ],
            'results' => $results,
        ];

        $jsonOut = $this->encodeJson($payload, $pretty);
        if ($jsonOut === null) {
            $this->error('Failed to encode output JSON');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn("[dry] would write: storage/app/{$outStoragePath}");
            return self::SUCCESS;
        }

        Storage::disk('local')->put($outStoragePath, $jsonOut);
        $this->info("Wrote: storage/app/{$outStoragePath}");

        return self::SUCCESS;
    }

    /**
     * Upsert-like merge:
     * - name_en: 既に code のままなら、より良い nameEn が来た時だけ上書き
     * - name_jp: 既存countries.jsonの値を優先（merge-existing）
     * - region : null の時だけ埋める
     */
    private function upsertCountryRow(array &$countryMap, string $code, ?string $nameEn, array $existingJp, ?string $region): void
    {
        $code = strtoupper(trim($code));
        if ($code === '') return;

        if (!isset($countryMap[$code])) {
            $countryMap[$code] = [
                'state_party_code' => $code,
                'name_en' => ($nameEn !== null && trim($nameEn) !== '') ? $nameEn : $code,
                'name_jp' => $existingJp[$code] ?? null,
                'region' => $region,
            ];
            return;
        }

        // name_en を改善できる場合だけ更新
        if ($nameEn !== null) {
            $nameEn = trim($nameEn);
            if ($nameEn !== '') {
                $current = (string)($countryMap[$code]['name_en'] ?? $code);
                if ($current === $code) {
                    $countryMap[$code]['name_en'] = $nameEn;
                }
            }
        }

        // region は欠損だけ埋める
        if (($countryMap[$code]['region'] ?? null) === null && $region !== null) {
            $countryMap[$code]['region'] = $region;
        }

        // name_jp は existing を優先（countryMap作成時に設定済み）
        if (($countryMap[$code]['name_jp'] ?? null) === null && isset($existingJp[$code])) {
            $countryMap[$code]['name_jp'] = $existingJp[$code];
        }
    }

    private function normalizeRegionCode(mixed $v): ?string
    {
        if (!is_string($v)) return null;

        $code = strtoupper(trim($v));
        if ($code === '') return null;

        $allowed = ['EUR', 'AFR', 'APA', 'ARB', 'LAC'];
        if (!in_array($code, $allowed, true)) return null;

        return $code;
    }

    // ---- existing helpers (unchanged) ----

    private function collectJsonFiles(string $path): array
    {
        if (is_file($path)) {
            return str_ends_with($path, '.json') ? [$path] : [];
        }

        if (!is_dir($path)) return [];

        $files = [];
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.json')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;

        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;

        return base_path($path);
    }

    private function extractRows(array $json): ?array
    {
        if (array_key_exists('results', $json)) {
            return is_array($json['results']) ? $json['results'] : null;
        }

        return array_is_list($json) ? $json : null;
    }

    private function readExistingCountriesJpMap(string $storageOutPath): array
    {
        if (!Storage::disk('local')->exists($storageOutPath)) return [];

        $raw = (string) Storage::disk('local')->get($storageOutPath);
        $json = json_decode($raw, true);
        if (!is_array($json)) return [];

        $rows = $this->extractRows($json);
        if ($rows === null) return [];

        $map = [];
        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $code = strtoupper(trim((string)($row['state_party_code'] ?? '')));
            if ($code === '') continue;

            $jp = $row['name_jp'] ?? null;
            if (is_string($jp)) $jp = trim($jp);
            if ($jp === '') $jp = null;

            if ($jp !== null) $map[$code] = $jp;
        }

        ksort($map, SORT_STRING);
        return $map;
    }

    private function normalizeStringList(mixed $v): array
    {
        if (!is_array($v)) return [];

        $out = [];
        foreach ($v as $x) {
            if (!is_string($x)) continue;
            $x = trim($x);
            if ($x === '') continue;
            $out[] = $x;
        }

        return $this->uniqueList($out);
    }

    private function normalizeCodeList(mixed $v): array
    {
        $out = [];

        if (is_array($v)) {
            foreach ($v as $x) {
                if (!is_string($x)) continue;
                $x = strtoupper(trim($x));
                if ($x !== '') $out[] = $x;
            }
            return $this->uniqueList($out);
        }

        if (is_string($v)) {
            $s = trim($v);
            if ($s === '') return [];

            $parts = preg_split('/[,\|;\/\s]+/', $s) ?: [];
            foreach ($parts as $p) {
                $p = strtoupper(trim($p));
                if ($p !== '') $out[] = $p;
            }
            return $this->uniqueList($out);
        }

        return [];
    }

    private function uniqueList(array $list): array
    {
        $seen = [];
        $out = [];
        foreach ($list as $v) {
            if (isset($seen[$v])) continue;
            $seen[$v] = true;
            $out[] = $v;
        }
        return $out;
    }

    private function encodeJson(mixed $payload, bool $pretty): ?string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($payload, $flags);
        return $json === false ? null : $json;
    }
}
