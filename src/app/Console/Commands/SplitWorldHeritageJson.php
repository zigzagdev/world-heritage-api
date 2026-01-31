<?php

namespace App\Console\Commands;

use App\Support\CountryCodeNormalizer;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SplitWorldHeritageJson extends Command
{
    protected $signature = 'world-heritage:split-json
    {--in=private/unesco/world-heritage-sites.json : Input raw UNESCO JSON file (dump output) in storage/app/...}
    {--out=private/unesco/normalized : Output dir in storage/app/... (directory)}
    {--site-judgements-out=private/unesco/normalized/site-country-judgements.json : Per-site judgement output (all rows)}
    {--exceptions-out=private/unesco/normalized/exceptions-missing-iso-codes.json : Missing/invalid iso_codes rows (subset)}
    {--exceptions-limit=2000 : Max number of exception rows to store}
    {--pretty : Pretty print JSON}
    {--log-limit=50 : Max number of skipped/invalid log lines}
    {--summary-file= : Optional summary JSON file path (in storage/app/...)}
    {--strict : Fail if any invalid/unknown rows exist}
    {--clean : Delete existing *.json in output dir before writing}
    {--dry-run : Do not write files (only logs/summary)}';

    protected $description = 'Normalize raw UNESCO JSON into DB-import-ready JSON files for local DB tables';

    public function handle(): int
    {
        $in = trim((string)$this->option('in'));
        $out = trim((string)$this->option('out'));

        $pretty = (bool)$this->option('pretty');
        $logLimit = max(0, (int)$this->option('log-limit'));
        $summaryFile = trim((string)$this->option('summary-file'));
        $strict = (bool)$this->option('strict');
        $clean = (bool)$this->option('clean');
        $dryRun = (bool)$this->option('dry-run');

        $siteJudgementsOut = trim((string)$this->option('site-judgements-out'));
        $exceptionsOut = trim((string)$this->option('exceptions-out'));
        $exceptionsLimit = max(0, (int)$this->option('exceptions-limit'));

        if ($in === '') {
            $this->error('Missing required option: --in');
            return self::FAILURE;
        }

        $inPath = $this->resolvePathToFile($in);
        if (!is_file($inPath)) {
            $this->error("Input JSON not found: {$inPath}");
            return self::FAILURE;
        }

        $raw = @file_get_contents($inPath);
        if ($raw === false) {
            $this->error("Failed to read input file: {$inPath}");
            return self::FAILURE;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $this->error("Invalid JSON: {$inPath}");
            return self::FAILURE;
        }

        $meta = $json['meta'] ?? null;
        $results = $json['results'] ?? null;
        if (!is_array($meta) || !is_array($results)) {
            $this->error('Invalid raw format: expected {"meta":{...},"results":[...]}');
            return self::FAILURE;
        }
        if ($results === []) {
            $this->error("No results in JSON: {$inPath}");
            return self::FAILURE;
        }

        $outDir = $this->resolvePathToDir($out);
        if (!is_dir($outDir)) {
            if (!@mkdir($outDir, 0777, true) && !is_dir($outDir)) {
                $this->error("Failed to create output dir: {$outDir}");
                return self::FAILURE;
            }
        }

        if ($clean && !$dryRun) {
            $this->cleanOutputDir($outDir);
        }

        $this->info("Input: {$inPath}");
        $this->info('Rows (raw results): ' . count($results));
        $this->info("Output dir: {$outDir}");
        if ($dryRun) $this->warn('Dry-run enabled: will NOT write any files.');

        $normalizer = app(CountryCodeNormalizer::class);

        $logged = 0;
        $logSkip = function (string $reason, int $index, mixed $idNo = null, array $extra = []) use ($logLimit, &$logged): void {
            if ($logLimit <= 0) return;
            if ($logged >= $logLimit) return;

            $idPart = ($idNo !== null && $idNo !== '') ? " id_no={$idNo}" : '';
            $extraPart = $extra !== [] ? ' extra=' . json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $this->warn("[skip] index={$index}{$idPart} reason={$reason}{$extraPart}");
            $logged++;
        };

        $sites = [];
        $countries = [];
        $pivot = [];
        $images = [];
        $siteJudgements = [];
        $exceptions = [];

        $invalid = 0;
        $rowsMissingId = 0;
        $rowsNonNumericId = 0;
        $rowsMissingCodes = 0;
        $rowsUnknownCodes = 0;

        $transnationalCount = 0;
        $transnationalExamples = [];
        $transnationalExampleLimit = 25;

        foreach ($results as $i => $row) {
            $i = (int)$i;

            if (!is_array($row)) {
                $invalid++;
                $logSkip('row_not_object', $i, null);

                $siteJudgements[] = $this->buildJudgement($i, null, null, null, null, [], [], 'unresolved', 'row_not_object');

                if (count($exceptions) < $exceptionsLimit) {
                    $exceptions[] = [
                        'index' => $i,
                        'site_id' => null,
                        'reason' => 'row_not_object',
                        'row_type' => gettype($row),
                    ];
                }
                continue;
            }

            $idNoRaw = trim((string)($row['id_no'] ?? ($row['id'] ?? '')));
            if ($idNoRaw === '') {
                $rowsMissingId++;
                $logSkip('id_no_missing', $i, null);

                $siteJudgements[] = $this->buildJudgement(
                    $i,
                    null,
                    $row['name_en'] ?? null,
                    $row['region_code'] ?? null,
                    $row['iso_codes'] ?? null,
                    [],
                    [],
                    'unresolved',
                    'id_no_missing'
                );

                if (count($exceptions) < $exceptionsLimit) {
                    $exceptions[] = [
                        'index' => $i,
                        'site_id' => null,
                        'name_en' => $row['name_en'] ?? null,
                        'reason' => 'id_no_missing',
                        'region_code' => $row['region_code'] ?? null,
                        'iso_codes_raw' => $row['iso_codes'] ?? null,
                    ];
                }
                continue;
            }

            if (!is_numeric($idNoRaw)) {
                $rowsNonNumericId++;
                $logSkip('id_no_not_numeric', $i, $idNoRaw);

                $siteJudgements[] = $this->buildJudgement(
                    $i,
                    $idNoRaw,
                    $row['name_en'] ?? null,
                    $row['region_code'] ?? null,
                    $row['iso_codes'] ?? null,
                    [],
                    [],
                    'unresolved',
                    'id_no_not_numeric'
                );

                if (count($exceptions) < $exceptionsLimit) {
                    $exceptions[] = [
                        'index' => $i,
                        'site_id' => $idNoRaw,
                        'name_en' => $row['name_en'] ?? null,
                        'reason' => 'id_no_not_numeric',
                        'region_code' => $row['region_code'] ?? null,
                        'iso_codes_raw' => $row['iso_codes'] ?? null,
                    ];
                }
                continue;
            }

            $siteId = (int)$idNoRaw;

            // site 本体は常に作る
            if (!isset($sites[$siteId])) {
                $sites[$siteId] = $this->normalizeSiteRowImportReady($row, $siteId);
            } else {
                $sites[$siteId] = $this->mergeSiteRowPreferExisting($sites[$siteId], $row);
            }

            $region = $this->normalizeRegionCodeOrFallback($row['region_code'] ?? null);

            // Country judgement (iso2 -> iso3 list)
            $iso2 = $this->extractIsoCodes($row['iso_codes'] ?? null);
            $iso3 = [];
            $reason = null;
            $status = 'ok';

            if ($iso2 === []) {
                $rowsMissingCodes++;
                $status = 'unresolved';
                $reason = 'iso_codes_missing_or_empty';
                $logSkip('iso_codes_missing_or_empty', $i, $siteId);
            } else {
                try {
                    $iso3 = $normalizer->toIso3List($iso2);
                    if ($iso3 === []) {
                        $rowsMissingCodes++;
                        $status = 'unresolved';
                        $reason = 'iso3_empty_after_normalize';
                        $logSkip('iso3_empty_after_normalize', $i, $siteId, ['codes' => $iso2]);
                    }
                } catch (InvalidArgumentException $e) {
                    $rowsUnknownCodes++;
                    $invalid++;
                    $status = 'unresolved';
                    $reason = 'iso_codes_unknown';
                    $logSkip('iso_codes_unknown', $i, $siteId, ['codes' => $iso2, 'msg' => $e->getMessage()]);
                    if ($strict) throw $e;

                    if (count($exceptions) < $exceptionsLimit) {
                        $exceptions[] = [
                            'index' => $i,
                            'site_id' => $siteId,
                            'name_en' => $row['name_en'] ?? null,
                            'reason' => $reason,
                            'message' => $e->getMessage(),
                            'region_code' => $row['region_code'] ?? null,
                            'iso_codes_raw' => $row['iso_codes'] ?? null,
                            'iso2' => $iso2,
                        ];
                    }
                }
            }

            $siteJudgements[] = $this->buildJudgement(
                $i,
                $siteId,
                $row['name_en'] ?? null,
                $row['region_code'] ?? null,
                $row['iso_codes'] ?? null,
                $iso2,
                $iso3,
                $status,
                $reason
            );

            /**
             * ✅ ここが修正点：images は国コード判定に依存させない
             * - iso_codes が null でも images_urls は入ってくる (148 がそれ)
             */
            $imageUrls = $this->extractImageUrls($row);
            if (count($imageUrls) >= 2) { // rule: only_sites_with_multiple_images
                foreach ($imageUrls as $idx => $url) {
                    $images[] = [
                        'world_heritage_site_id' => $siteId,
                        'url' => $url,
                        'sort_order' => $idx,
                        'is_primary' => ($idx === 0) ? 1 : 0,
                    ];
                }
            }

            // unresolved の場合は countries/pivot だけ作らない
            if ($status !== 'ok') {
                if (count($exceptions) < $exceptionsLimit) {
                    $exceptions[] = [
                        'index' => $i,
                        'site_id' => $siteId,
                        'name_en' => $row['name_en'] ?? null,
                        'reason' => $reason,
                        'region_code' => $row['region_code'] ?? null,
                        'iso_codes_raw' => $row['iso_codes'] ?? null,
                        'iso2' => $iso2,
                        'states_names' => $row['states_names'] ?? null,
                        'raw_keys' => array_slice(array_keys($row), 0, 40),
                    ];
                }
                continue;
            }

            // Country rows and pivot rows
            $names = $this->normalizeStatesNames($row['states_names'] ?? null);

            if (count($iso3) > 1) {
                $transnationalCount++;
                if (count($transnationalExamples) < $transnationalExampleLimit) {
                    $transnationalExamples[] = [
                        'index' => $i,
                        'site_id' => $siteId,
                        'iso3' => $iso3,
                        'states_names' => $names,
                    ];
                }
            }

            foreach ($iso3 as $idx => $code3) {
                // countries.json row
                if (!isset($countries[$code3])) {
                    $countries[$code3] = [
                        'state_party_code' => $code3,
                        'name_en' => $names[$idx] ?? $names[0] ?? $code3,
                        'name_jp' => null,
                        'region' => $region,
                    ];
                } else {
                    if (($countries[$code3]['name_en'] ?? null) === $code3) {
                        $better = $names[$idx] ?? $names[0] ?? null;
                        if (is_string($better) && trim($better) !== '') {
                            $countries[$code3]['name_en'] = trim($better);
                        }
                    }
                    if (($countries[$code3]['region'] ?? null) === null && $region !== null) {
                        $countries[$code3]['region'] = $region;
                    }
                }

                // pivot
                $k = "{$siteId}|{$code3}";
                if (!isset($pivot[$k])) {
                    $pivot[$k] = [
                        'world_heritage_site_id' => $siteId,
                        'state_party_code' => $code3,
                        'is_primary' => ($idx === 0) ? 1 : 0,
                        'inscription_year' => isset($row['date_inscribed']) ? (int)$row['date_inscribed'] : null,
                    ];
                }
            }
        }

        if ($strict) {
            $fail = ($invalid > 0)
                || ($rowsMissingId > 0)
                || ($rowsNonNumericId > 0)
                || ($rowsMissingCodes > 0)
                || ($rowsUnknownCodes > 0);

            if ($fail) {
                $this->error('Strict mode: invalid rows or unresolved country judgements exist.');
                return self::FAILURE;
            }
        }

        ksort($sites, SORT_NUMERIC);
        ksort($countries, SORT_STRING);
        ksort($pivot, SORT_STRING);

        // Payloads (import-ready)
        $sitesPayload = [
            'meta' => [
                'schema' => 'world_heritage_sites.import.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'sites' => count($sites),
                'target_table' => 'world_heritage_sites',
            ],
            'results' => array_values($sites),
        ];

        $countriesPayload = [
            'meta' => [
                'schema' => 'countries.import.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'countries' => count($countries),
                'target_table' => 'countries',
            ],
            'results' => array_values($countries),
        ];

        $pivotPayload = [
            'meta' => [
                'schema' => 'site_state_parties.import.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'relations' => count($pivot),
                'target_table' => 'site_state_parties',
            ],
            'results' => array_values($pivot),
        ];

        $imagesPayload = [
            'meta' => [
                'schema' => 'world_heritage_site_images.import.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'images' => count($images),
                'target_table' => 'world_heritage_site_images',
                'rule' => 'only_sites_with_multiple_images',
            ],
            'results' => $images,
        ];

        $judgementsPayload = [
            'meta' => [
                'schema' => 'site_country_judgements.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'judgements' => count($siteJudgements),
                'country_code_standard' => 'alpha-3',
                'status_values' => ['ok', 'unresolved'],
            ],
            'results' => $siteJudgements,
        ];

        $exceptionsPayload = [
            'meta' => [
                'schema' => 'site_country_exceptions.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'exceptions' => count($exceptions),
                'limit' => $exceptionsLimit,
            ],
            'results' => $exceptions,
        ];

        // Write files that are meant to be imported directly
        $written = [
            'world_heritage_sites.json' => $sitesPayload,
            'countries.json' => $countriesPayload,
            'site_state_parties.json' => $pivotPayload,
            'world_heritage_site_images.json' => $imagesPayload,
        ];

        foreach ($written as $filename => $payload) {
            $encoded = $this->encodeJson($payload, $pretty);
            if ($encoded === null) {
                $this->error("Failed to encode: {$filename}");
                return self::FAILURE;
            }

            $filePath = rtrim($outDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

            if ($dryRun) {
                $this->info("[dry] would write {$filePath} (" . count($payload['results'] ?? []) . " records)");
                continue;
            }

            $ok = @file_put_contents($filePath, $encoded);
            if ($ok === false) {
                $this->error("Failed to write: {$filePath}");
                return self::FAILURE;
            }

            $this->info("Wrote {$filePath} (" . count($payload['results'] ?? []) . " records)");
        }

        // Judgements + exceptions (diagnostics)
        $judgementsPath = $this->resolvePathToFile($siteJudgementsOut);
        $exceptionsPath = $this->resolvePathToFile($exceptionsOut);

        $encodedJudgements = $this->encodeJson($judgementsPayload, $pretty);
        if ($encodedJudgements === null) {
            $this->error('Failed to encode judgements JSON');
            return self::FAILURE;
        }

        $encodedExceptions = $this->encodeJson($exceptionsPayload, $pretty);
        if ($encodedExceptions === null) {
            $this->error('Failed to encode exceptions JSON');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info("[dry] would write {$judgementsPath} (" . count($judgementsPayload['results']) . " records)");
            $this->info("[dry] would write {$exceptionsPath} (" . count($exceptionsPayload['results']) . " records)");
        } else {
            if (@file_put_contents($judgementsPath, $encodedJudgements) === false) {
                $this->error("Failed to write: {$judgementsPath}");
                return self::FAILURE;
            }
            $this->info("Wrote {$judgementsPath} (" . count($judgementsPayload['results']) . " records)");

            if (@file_put_contents($exceptionsPath, $encodedExceptions) === false) {
                $this->error("Failed to write: {$exceptionsPath}");
                return self::FAILURE;
            }
            $this->info("Wrote {$exceptionsPath} (" . count($exceptionsPayload['results']) . " records)");
        }

        $this->line('----');
        $this->info('Sites (unique id_no): ' . count($sites));
        $this->info('Countries (unique ISO3): ' . count($countries));
        $this->info('Site-State relations: ' . count($pivot));
        $this->info('Site images (rows): ' . count($images));
        $this->info('Site country judgements (rows): ' . count($siteJudgements));
        $this->info('Exceptions collected: ' . count($exceptions));
        $this->info("Invalid: {$invalid}");
        $this->info("Missing id_no: {$rowsMissingId}, Non-numeric id_no: {$rowsNonNumericId}");
        $this->info("Missing/empty iso_codes or iso3: {$rowsMissingCodes}, Unknown iso_codes: {$rowsUnknownCodes}");
        $this->info("Transnational rows detected (countries>=2): {$transnationalCount}");

        if ($transnationalExamples !== []) {
            $this->warn('Transnational examples (up to 25):');
            foreach ($transnationalExamples as $ex) {
                $this->line('- ' . json_encode($ex, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }

        if ($logLimit > 0 && $logged >= $logLimit) {
            $this->warn("Skip logs truncated (log-limit={$logLimit})");
        }

        $summary = [
            'meta' => [
                'source_raw' => $in,
                'input_path_resolved' => $inPath,
                'output_dir_resolved' => $outDir,
                'split_at' => now()->toIso8601String(),
                'dry_run' => $dryRun,
                'clean' => $clean,
                'strict' => $strict,
            ],
            'counts' => [
                'input_rows' => count($results),
                'sites' => count($sites),
                'countries' => count($countries),
                'site_state_relations' => count($pivot),
                'site_images' => count($images),
                'site_country_judgements' => count($siteJudgements),
                'exceptions' => count($exceptions),
                'invalid' => $invalid,
                'missing_id_no' => $rowsMissingId,
                'non_numeric_id_no' => $rowsNonNumericId,
                'missing_or_empty_iso' => $rowsMissingCodes,
                'unknown_iso' => $rowsUnknownCodes,
                'transnational_rows' => $transnationalCount,
            ],
            'transnational_examples' => $transnationalExamples,
        ];

        if ($summaryFile !== '') {
            $summaryPath = $this->resolvePathToFile($summaryFile);
            $encodedSummary = $this->encodeJson($summary, true);
            if ($encodedSummary === null) {
                $this->warn("Failed to encode summary JSON: {$summaryPath}");
            } else {
                if (!$dryRun) {
                    $ok = @file_put_contents($summaryPath, $encodedSummary);
                    if ($ok === false) $this->warn("Failed to write summary: {$summaryPath}");
                    else $this->info("Wrote summary: {$summaryPath}");
                } else {
                    $this->info("[dry] would write summary: {$summaryPath}");
                }
            }
        }

        return self::SUCCESS;
    }

    private function buildJudgement(
        int $index,
        mixed $siteId,
        mixed $nameEn,
        mixed $regionCode,
        mixed $isoCodesRaw,
        array $iso2,
        array $iso3,
        string $status,
        ?string $reason
    ): array {
        return [
            'index' => $index,
            'site_id' => $siteId,
            'name_en' => is_scalar($nameEn) ? (string)$nameEn : null,
            'region_code' => is_scalar($regionCode) ? (string)$regionCode : null,
            'iso_codes_raw' => $isoCodesRaw,
            'iso2' => $iso2,
            'iso3' => $iso3,
            'status' => $status,
            'reason' => $reason,
        ];
    }

    private function extractIsoCodes(mixed $v): array
    {
        if (!is_string($v)) return [];
        $s = trim($v);
        if ($s === '') return [];

        $parts = array_map('trim', explode(',', $s));
        $out = [];
        $seen = [];
        foreach ($parts as $p) {
            $p = strtoupper($p);
            if ($p === '') continue;
            if (!isset($seen[$p])) {
                $seen[$p] = true;
                $out[] = $p;
            }
        }
        return $out;
    }

    private function normalizeStatesNames(mixed $statesNames): array
    {
        if (!is_array($statesNames)) return [];
        $seen = [];
        $out = [];
        foreach ($statesNames as $v) {
            $name = trim((string)$v);
            if ($name === '') continue;
            if (!isset($seen[$name])) {
                $seen[$name] = true;
                $out[] = $name;
            }
        }
        return $out;
    }

    private function extractImageUrls(array $row): array
    {
        $urls = [];

        $main = $row['main_image_url']['url'] ?? null;
        if (is_string($main)) {
            $main = trim($main);
            if ($main !== '') $urls[] = $main;
        }

        $images = $row['images_urls'] ?? null;

        if (is_string($images)) {
            $parts = preg_split('/\s*,\s*/', trim($images)) ?: [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') $urls[] = $p;
            }
        }

        if (is_array($images)) {
            foreach ($images as $p) {
                if (!is_string($p)) continue;
                $p = trim($p);
                if ($p !== '') $urls[] = $p;
            }
        }

        $seen = [];
        $out = [];
        foreach ($urls as $u) {
            if (isset($seen[$u])) continue;
            $seen[$u] = true;
            $out[] = $u;
        }
        return $out;
    }

    private function normalizeSiteRowImportReady(array $row, int $siteId): array
    {
        $lat = $row['coordinates']['lat'] ?? null;
        $lon = $row['coordinates']['lon'] ?? null;

        $region = $this->normalizeRegionCodeOrFallback($row['region_code'] ?? null);
        $category = $this->normalizeCategoryOrFallback($row['category'] ?? null);
        $criteria = $this->resolveCriteriaList($row);

        $toBool = function ($v): bool {
            if (is_bool($v)) return $v;
            if (is_int($v) || is_float($v)) return ((int)$v) === 1;

            $s = strtolower(trim((string)$v));
            return in_array($s, ['1', 'true', 't', 'yes', 'y', 'on'], true);
        };
        $toTinyInt = fn($v) => $toBool($v) ? 1 : 0;

        $iso2List = $this->extractIsoCodes($row['iso_codes'] ?? null);

        $stateParty = null;
        $country = null;
        if (count($iso2List) === 1) {
            $iso3 = $this->toIso3OrNull($iso2List[0]);
            $stateParty = $iso3;
            $country = $iso3;
        }

        $main = $row['main_image_url']['url'] ?? null;
        $imageUrl = null;

        if (is_string($main)) {
            $main = trim($main);
            if ($main !== '') {
                $imageUrl = mb_substr($main, 0, 255);
            }
        }

        $primaryImageUrl = null;

        $year = isset($row['date_inscribed']) && is_numeric($row['date_inscribed'])
            ? (int)$row['date_inscribed']
            : 0;

        return [
            'id' => $siteId,
            'official_name' => $this->stringOrFallback($row['official_name'] ?? ($row['name_en'] ?? null), (string)$siteId),
            'name' => $this->stringOrFallback($row['name_en'] ?? null, (string)$siteId),
            'name_jp' => $this->stringOrNull($row['name_jp'] ?? null),
            'country' => $country,
            'region' => $region,
            'state_party' => $stateParty,
            'category' => $category,
            'criteria' => $criteria,
            'year_inscribed' => $year,
            'area_hectares' => isset($row['area_hectares']) && is_numeric($row['area_hectares']) ? (float)$row['area_hectares'] : null,
            'buffer_zone_hectares' => isset($row['buffer_zone_hectares']) && is_numeric($row['buffer_zone_hectares']) ? (float)$row['buffer_zone_hectares'] : null,
            'is_endangered' => $toTinyInt(
                $row['danger'] ?? $row['is_endangered'] ?? false
            ),
            'latitude' => is_numeric($lat) ? (float)$lat : null,
            'longitude' => is_numeric($lon) ? (float)$lon : null,
            'short_description' => $this->stringOrNull($row['short_description_en'] ?? null),
            'image_url' => $imageUrl,
            'primary_image_url' => $primaryImageUrl,
            'thumbnail_image_id' => null,
            'unesco_site_url' => $this->stringOrNull($row['unesco_site_url'] ?? ($row['url'] ?? null)),
            'created_at' => null,
            'updated_at' => null,
            'deleted_at' => null,
        ];
    }

    private function mergeSiteRowPreferExisting(array $existing, array $incoming): array
    {
        $fill = function (string $key, mixed $value) use (&$existing): void {
            if (!array_key_exists($key, $existing) || $existing[$key] === null || $existing[$key] === '') {
                if ($value !== null && $value !== '') $existing[$key] = $value;
            }
        };

        $fill('official_name', $incoming['official_name'] ?? ($incoming['name_en'] ?? null));
        $fill('name', $incoming['name_en'] ?? null);
        $fill('name_jp', $incoming['name_jp'] ?? null);

        if (($existing['region'] ?? null) === null || $existing['region'] === '') {
            $existing['region'] = $this->normalizeRegionCodeOrFallback($incoming['region_code'] ?? null);
        }
        if (($existing['category'] ?? null) === null || $existing['category'] === '') {
            $existing['category'] = $this->normalizeCategoryOrFallback($incoming['category'] ?? null);
        }

        if (!isset($existing['criteria']) || !is_array($existing['criteria']) || $existing['criteria'] === []) {
            $existing['criteria'] = $this->resolveCriteriaList($incoming);
        }

        if (!isset($existing['year_inscribed']) || !is_numeric($existing['year_inscribed'])) {
            $existing['year_inscribed'] = (isset($incoming['date_inscribed']) && is_numeric($incoming['date_inscribed']))
                ? (int)$incoming['date_inscribed']
                : 0;
        }

        if (($existing['state_party'] ?? null) === null) {
            $iso2List = $this->extractIsoCodes($incoming['iso_codes'] ?? null);
            if (count($iso2List) === 1) {
                $sp = $this->toIso3OrNull($iso2List[0]);
                if ($sp !== null) {
                    $existing['state_party'] = $sp;
                    if (($existing['country'] ?? null) === null) $existing['country'] = $sp;
                }
            }
        }

        $fill('area_hectares', isset($incoming['area_hectares']) ? (is_numeric($incoming['area_hectares']) ? (float)$incoming['area_hectares'] : null) : null);
        $fill('buffer_zone_hectares', isset($incoming['buffer_zone_hectares']) ? (is_numeric($incoming['buffer_zone_hectares']) ? (float)$incoming['buffer_zone_hectares'] : null) : null);

        if (isset($incoming['coordinates']['lat']) && ($existing['latitude'] ?? null) === null) {
            $existing['latitude'] = is_numeric($incoming['coordinates']['lat']) ? (float)$incoming['coordinates']['lat'] : null;
        }
        if (isset($incoming['coordinates']['lon']) && ($existing['longitude'] ?? null) === null) {
            $existing['longitude'] = is_numeric($incoming['coordinates']['lon']) ? (float)$incoming['coordinates']['lon'] : null;
        }

        $fill('short_description', $incoming['short_description_en'] ?? null);

        if (($existing['image_url'] ?? null) === null || $existing['image_url'] === '') {
            $main = $incoming['main_image_url']['url'] ?? null;
            if (is_string($main)) {
                $main = trim($main);
                if ($main !== '') {
                    $existing['image_url'] = mb_substr($main, 0, 255);
                }
            }
        }

        // ここは元コード通り：常に null に戻す挙動（意味が薄いが、そのまま）
        if (($existing['primary_image_url'] ?? null) !== null) {
            $existing['primary_image_url'] = null;
        }

        if (($existing['unesco_site_url'] ?? null) === null) {
            $u = $incoming['unesco_site_url'] ?? ($incoming['url'] ?? null);
            if ($u) $existing['unesco_site_url'] = $u;
        }

        return $existing;
    }

    private function normalizeRegionCodeOrFallback(mixed $v): string
    {
        if (!is_string($v)) return 'UNK';
        $code = strtoupper(trim($v));
        if ($code === '') return 'UNK';

        $allowed = ['EUR', 'AFR', 'APA', 'ARB', 'LAC'];
        return in_array($code, $allowed, true) ? $code : 'UNK';
    }

    private function normalizeCategoryOrFallback(mixed $v): string
    {
        if (!is_string($v)) return 'Cultural';
        $s = trim($v);
        if ($s === '') return 'Cultural';

        $allowed = ['Cultural', 'Natural', 'Mixed'];
        return in_array($s, $allowed, true) ? $s : 'Cultural';
    }

    private function stringOrNull(mixed $v): ?string
    {
        if (!is_scalar($v)) return null;
        $s = trim((string)$v);
        return $s === '' ? null : $s;
    }

    private function stringOrFallback(mixed $v, string $fallback): string
    {
        if (!is_scalar($v)) return $fallback;
        $s = trim((string)$v);
        return $s === '' ? $fallback : $s;
    }

    private function resolvePathToDir(string $path): string
    {
        $path = trim($path);
        if ($path === '') return storage_path('app');

        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }
        return storage_path('app/' . ltrim($path, '/'));
    }

    private function resolvePathToFile(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;

        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }
        return storage_path('app/' . ltrim($path, '/'));
    }

    private function encodeJson(mixed $payload, bool $pretty): ?string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($payload, $flags);
        return $json === false ? null : $json;
    }

    private function cleanOutputDir(string $outDir): void
    {
        $pattern = rtrim($outDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.json';
        $files = glob($pattern) ?: [];
        $deleted = 0;

        foreach ($files as $f) {
            if (!is_file($f)) continue;
            if (@unlink($f)) $deleted++;
        }

        $this->warn("Cleaned output dir: deleted {$deleted} json files");
    }

    private function extractCriteriaList(mixed $criteriaTxt): array
    {
        if (!is_string($criteriaTxt)) return [];
        $s = trim($criteriaTxt);
        if ($s === '') return [];

        preg_match_all('/\(([ivx]+)\)/i', $s, $m);
        if (!isset($m[1]) || !is_array($m[1])) return [];

        $out = [];
        $seen = [];
        foreach ($m[1] as $v) {
            $v = strtolower(trim((string)$v));
            if ($v === '') continue;
            if (!isset($seen[$v])) {
                $seen[$v] = true;
                $out[] = $v;
            }
        }
        return $out;
    }

    private function resolveCriteriaList(array $row): array
    {
        $criteria = $this->extractCriteriaList($row['criteria_txt'] ?? null);
        if ($criteria !== []) return $criteria;

        $criteria = $this->extractCriteriaList($row['criteria'] ?? null);
        if ($criteria !== []) return $criteria;

        $criteria = $this->extractCriteriaFromJustification($row['justification_en'] ?? null);
        if ($criteria !== []) return $criteria;

        return [];
    }

    private function extractCriteriaFromJustification(mixed $justificationEn): array
    {
        if (!is_string($justificationEn)) return [];
        $s = trim($justificationEn);
        if ($s === '') return [];

        $pos = stripos($s, 'criterion');
        if ($pos === false) $pos = stripos($s, 'criteria');
        if ($pos === false) return [];

        $slice = substr($s, $pos, 600);

        preg_match_all('/\(([ivx]+)\)/i', $slice, $m);
        if (!isset($m[1]) || !is_array($m[1]) || $m[1] === []) return [];

        $out = [];
        $seen = [];
        foreach ($m[1] as $v) {
            $v = strtolower(trim((string)$v));
            if ($v === '') continue;
            if (!isset($seen[$v])) {
                $seen[$v] = true;
                $out[] = $v;
            }
        }

        return $out;
    }

    private function toIso3OrNull(string $code): ?string
    {
        $code = strtoupper(trim($code));
        if ($code === '') return null;

        if (strlen($code) === 3 && ctype_alpha($code)) {
            return $code;
        }

        if (!(strlen($code) === 2 && ctype_alpha($code))) {
            if ((bool)$this->option('strict')) {
                throw new InvalidArgumentException("Unknown country code format: {$code}");
            }
            return null;
        }

        $normalizer = app(CountryCodeNormalizer::class);

        try {
            $list = $normalizer->toIso3List([$code]);
            return $list[0] ?? null;
        } catch (InvalidArgumentException $e) {
            if ((bool)$this->option('strict')) throw $e;
            return null;
        }
    }
}
