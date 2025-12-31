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
        {--out=private/country/normalized/countries.json : Output path in storage/app/... }
        {--sites-out=private/country/normalized/site-country-codes.json : Output path for per-site country judgement}
        {--exceptions-out=private/country/normalized/exceptions-missing-codes.json : Output path for rows missing/invalid country codes}
        {--pretty : Pretty print JSON}
        {--dry-run : Do not write output, only show counts}
        {--strict : Fail if any row cannot be mapped to at least one country code}
        {--merge-existing : Keep existing name_jp when countries.json already exists (recommended)}
        {--clean : If output exists, delete it before writing (name_jp merge will be skipped)}
        {--exceptions-limit=200 : Max number of missing/invalid-code rows to store in exceptions file}';

    protected $description = 'Extract/normalize country list from UNESCO JSON and also judge country per each world heritage row (iso3 or null)';

    public function handle(): int
    {
        $in     = trim((string) $this->option('in'));
        $out    = trim((string) $this->option('out'));
        $sitesOut = trim((string) $this->option('sites-out'));
        $exceptionsOut = trim((string) $this->option('exceptions-out'));

        $pretty = (bool) $this->option('pretty');
        $dryRun = (bool) $this->option('dry-run');
        $strict = (bool) $this->option('strict');
        $mergeExisting = (bool) $this->option('merge-existing');
        $clean  = (bool) $this->option('clean');
        $exceptionsLimit = max(0, (int)$this->option('exceptions-limit'));

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
        $sitesOutStoragePath = ltrim($sitesOut, '/');
        $exceptionsOutStoragePath = ltrim($exceptionsOut, '/');

        if ($clean && !$dryRun) {
            foreach ([$outStoragePath, $sitesOutStoragePath, $exceptionsOutStoragePath] as $p) {
                if (Storage::disk('local')->exists($p)) {
                    Storage::disk('local')->delete($p);
                    $this->warn("Deleted existing output: storage/app/{$p}");
                }
            }
        }

        // merge existing JP names
        $existingJp = [];
        if ($mergeExisting && !$clean) {
            $existingJp = $this->readExistingCountriesJpMap($outStoragePath);
            if ($existingJp !== []) {
                $this->info('Loaded existing name_jp entries: ' . count($existingJp));
            }
        }

        /** @var CountryCodeNormalizer $normalizer */
        $normalizer = app(CountryCodeNormalizer::class);

        // 国マスタ
        $countryMap = []; // key: ISO3

        // ★追加：全行について country 判定結果を必ず持つ
        $siteJudgements = []; // list
        $exceptions = []; // list (missing/invalid)
        $exceptionsCount = 0;

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

                $idNo = $row['id_no'] ?? null;
                $nameEn = $row['name_en'] ?? null;
                $statesNames = $row['states_names'] ?? null;
                $regionCode = $row['region_code'] ?? null;
                $codesRaw = $this->normalizeCodeList($row['states'] ?? $row['iso_codes'] ?? null);
                $judgement = [
                    'id_no' => is_scalar($idNo) ? (string)$idNo : null,
                    'name_en' => is_scalar($nameEn) ? (string)$nameEn : null,
                    'region_code' => is_scalar($regionCode) ? (string)$regionCode : null,
                    'states_names' => is_array($statesNames) ? $statesNames : null,
                    'raw_codes' => $codesRaw !== [] ? $codesRaw : null,
                    'iso3_codes' => null,
                    'status' => null,
                    'message' => null,
                ];

                if ($codesRaw === []) {
                    $rowsMissingCodes++;

                    $judgement['status'] = 'missing';
                    $judgement['message'] = 'iso_codes/states missing or empty';
                    $siteJudgements[] = $judgement;

                    if ($exceptionsLimit > 0 && $exceptionsCount < $exceptionsLimit) {
                        $exceptions[] = [
                            'file' => $file,
                            'exception_type' => 'missing_country_code',
                            'id_no' => $judgement['id_no'],
                            'name_en' => $judgement['name_en'],
                            'region_code' => $judgement['region_code'],
                            'states_names' => $judgement['states_names'],
                            'iso_codes' => $row['iso_codes'] ?? null,
                            'states' => $row['states'] ?? null,
                        ];
                        $exceptionsCount++;
                    }

                    if ($strict) {
                        $this->error('Strict mode: missing country code row detected.');
                        $this->line(json_encode($judgement, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                        return self::FAILURE;
                    }

                    continue;
                }

                try {
                    $codes3 = $normalizer->toIso3List($codesRaw);
                } catch (InvalidArgumentException $e) {
                    $rowsUnknownCodes++;

                    $judgement['status'] = 'unknown';
                    $judgement['message'] = $e->getMessage();
                    $siteJudgements[] = $judgement;

                    if (count($unknownSamples) < 10) {
                        $unknownSamples[] = [
                            'file' => $file,
                            'input_codes' => $codesRaw,
                            'message' => $e->getMessage(),
                        ];
                    }

                    if ($exceptionsLimit > 0 && $exceptionsCount < $exceptionsLimit) {
                        $exceptions[] = [
                            'file' => $file,
                            'exception_type' => 'unknown_country_code',
                            'id_no' => $judgement['id_no'],
                            'name_en' => $judgement['name_en'],
                            'region_code' => $judgement['region_code'],
                            'states_names' => $judgement['states_names'],
                            'raw_codes' => $codesRaw,
                            'message' => $e->getMessage(),
                        ];
                        $exceptionsCount++;
                    }

                    if ($strict) {
                        $this->error('Strict mode: unknown country code detected.');
                        $this->line($e->getMessage());
                        return self::FAILURE;
                    }

                    continue;
                }

                if ($codes3 === []) {
                    $rowsMissingCodes++;

                    $judgement['status'] = 'missing';
                    $judgement['message'] = 'empty after normalize';
                    $siteJudgements[] = $judgement;

                    if ($strict) {
                        $this->error('Strict mode: empty iso3 after normalize.');
                        $this->line(json_encode($judgement, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                        return self::FAILURE;
                    }

                    continue;
                }

                $judgement['status'] = 'ok';
                $judgement['iso3_codes'] = $codes3;
                $siteJudgements[] = $judgement;
                $names = $this->normalizeStringList($row['states_names'] ?? null);
                $region = $this->normalizeRegionCode($row['region_code'] ?? null);

                if ($names !== [] && count($names) === count($codes3)) {
                    foreach ($codes3 as $idx => $code) {
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

                foreach ($codes3 as $code) {
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
        $countries = array_values($countryMap);

        $this->line('----');
        $this->info('Input files: ' . count($files));
        $this->info("Input rows scanned: {$inputRows}");
        $this->info('Countries extracted (unique state_party_code): ' . count($countries));
        $this->info("Site judgements (rows): " . count($siteJudgements));
        $this->info("Invalid JSON files: {$invalidJsonFiles}");
        $this->info("Rows not object: {$rowsNotObject}");
        $this->info("Rows missing country codes: {$rowsMissingCodes}");
        $this->info("Rows unknown country codes: {$rowsUnknownCodes}");
        $this->info("Exceptions collected: " . count($exceptions));

        if ($unknownSamples !== []) {
            $this->warn('Unknown samples (up to 10):');
            foreach ($unknownSamples as $s) {
                $this->line('- ' . json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }

        $countriesPayload = [
            'meta' => [
                'schema' => 'countries.v1',
                'source' => 'unesco whc001',
                'country_code_standard' => 'alpha-3',
                'generated_at' => now()->toIso8601String(),
                'input' => $in,
                'input_files' => count($files),
                'input_rows_scanned' => $inputRows,
                'countries' => count($countries),
                'merge_existing_name_jp' => $mergeExisting && !$clean,
                'region_standard' => 'region_code(EUR/AFR/APA/ARB/LAC)',
            ],
            'results' => $countries,
        ];

        $sitesPayload = [
            'meta' => [
                'schema' => 'country_codes.v1',
                'source' => 'world-heritage-sites.json',
                'generated_at' => now()->toIso8601String(),
                'input' => $in,
                'input_files' => count($files),
                'input_rows_scanned' => $inputRows,
                'rows' => count($siteJudgements),
                'country_code_standard' => 'alpha-3',
                'null_means' => 'could_not_determine_country',
            ],
            'results' => $siteJudgements,
        ];

        $exceptionsPayload = [
            'meta' => [
                'schema' => 'exceptions_country_codes.v1',
                'generated_at' => now()->toIso8601String(),
                'input' => $in,
                'input_files' => count($files),
                'input_rows_scanned' => $inputRows,
                'exceptions' => count($exceptions),
                'limit' => $exceptionsLimit,
            ],
            'results' => $exceptions,
        ];

        $countriesJson = $this->encodeJson($countriesPayload, $pretty);
        $sitesJson = $this->encodeJson($sitesPayload, $pretty);
        $exceptionsJson = $this->encodeJson($exceptionsPayload, $pretty);

        if ($countriesJson === null || $sitesJson === null || $exceptionsJson === null) {
            $this->error('Failed to encode output JSON');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn("[dry] would write: storage/app/{$outStoragePath}");
            $this->warn("[dry] would write: storage/app/{$sitesOutStoragePath}");
            $this->warn("[dry] would write: storage/app/{$exceptionsOutStoragePath}");
            return self::SUCCESS;
        }

        Storage::disk('local')->put($outStoragePath, $countriesJson);
        Storage::disk('local')->put($sitesOutStoragePath, $sitesJson);
        Storage::disk('local')->put($exceptionsOutStoragePath, $exceptionsJson);

        $this->info("Wrote: storage/app/{$outStoragePath}");
        $this->info("Wrote: storage/app/{$sitesOutStoragePath}");
        $this->info("Wrote: storage/app/{$exceptionsOutStoragePath}");

        return self::SUCCESS;
    }

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

        if ($nameEn !== null) {
            $nameEn = trim($nameEn);
            if ($nameEn !== '') {
                $current = (string)($countryMap[$code]['name_en'] ?? $code);
                if ($current === $code) {
                    $countryMap[$code]['name_en'] = $nameEn;
                }
            }
        }

        if (($countryMap[$code]['region'] ?? null) === null && $region !== null) {
            $countryMap[$code]['region'] = $region;
        }

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
        return in_array($code, $allowed, true) ? $code : null;
    }

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

        $storageCandidate = storage_path('app/' . ltrim($path, '/'));
        if (file_exists($storageCandidate)) return $storageCandidate;

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
