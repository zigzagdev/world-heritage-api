<?php

namespace App\Console\Commands;

use App\Support\CountryCodeNormalizer;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SplitWorldHeritageJson extends Command
{
    protected $signature = 'world-heritage:split-json
        {--in= : Input raw JSON file (e.g. storage/app/unesco/raw/whc001-all.json)}
        {--out=private/unesco/normalized : Output path in storage/app/... }
        {--site-judgements-out=private/unesco/normalized/site-country-judgements.json : Per-site country judgement output (all rows)}
        {--exceptions-out=private/unesco/normalized/exceptions-missing-iso-codes.json : Missing/invalid iso_codes rows (subset)}
        {--exceptions-limit=2000 : Max number of exception rows to store}
        {--pretty : pretty print JSON}
        {--log-limit=50 : Max number of skipped/invalid log lines}
        {--summary-file= : Optional summary JSON file path (e.g. storage/app/unesco/split-summary.json)}
        {--strict : Fail if any invalid/unknown rows exist}
        {--clean : Delete existing *.json in output dir before writing}
        {--dry-run : Do not write files (only logs/summary)}';

    protected $description = 'Normalize raw UNESCO JSON into DB-import-ready JSON files (sites/state_parties/site_state_parties/site_images) + per-site country judgements';

    public function handle(): int
    {
        $in = (string) $this->option('in');
        $out = (string) $this->option('out');
        $pretty = (bool) $this->option('pretty');
        $logLimit = max(0, (int) $this->option('log-limit'));
        $summaryFile = trim((string) $this->option('summary-file'));
        $strict = (bool) $this->option('strict');
        $clean = (bool) $this->option('clean');
        $dryRun = (bool) $this->option('dry-run');

        $siteJudgementsOut = trim((string) $this->option('site-judgements-out'));
        $exceptionsOut = trim((string) $this->option('exceptions-out'));
        $exceptionsLimit = max(0, (int) $this->option('exceptions-limit'));

        if ($in === '') {
            $this->error('Missing required option: --in');
            return self::FAILURE;
        }

        $inPath = $this->resolvePath($in);
        if (!file_exists($inPath) || !is_file($inPath)) {
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

        $meta    = $json['meta'] ?? null;
        $results = $json['results'] ?? null;

        if (!is_array($meta) || !is_array($results)) {
            $this->error('Invalid raw format: expected {"meta":{...},"results":[...]}');
            return self::FAILURE;
        }

        if ($results === []) {
            $this->error("No results in JSON: {$inPath}");
            return self::FAILURE;
        }

        $outDir = $this->resolvePath($out);
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
        $parties = [];
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
            $i = (int) $i;

            if (!is_array($row)) {
                $invalid++;
                $logSkip('row_not_object', $i, null);

                $siteJudgements[] = [
                    'index' => $i,
                    'site_id' => null,
                    'name_en' => null,
                    'region_code' => null,
                    'iso_codes_raw' => null,
                    'iso2' => [],
                    'iso3' => [],
                    'status' => 'unresolved',
                    'reason' => 'row_not_object',
                ];

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

                $siteJudgements[] = [
                    'index' => $i,
                    'site_id' => null,
                    'name_en' => $row['name_en'] ?? null,
                    'region_code' => $row['region_code'] ?? null,
                    'iso_codes_raw' => $row['iso_codes'] ?? null,
                    'iso2' => [],
                    'iso3' => [],
                    'status' => 'unresolved',
                    'reason' => 'id_no_missing',
                ];

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

                $siteJudgements[] = [
                    'index' => $i,
                    'site_id' => $idNoRaw,
                    'name_en' => $row['name_en'] ?? null,
                    'region_code' => $row['region_code'] ?? null,
                    'iso_codes_raw' => $row['iso_codes'] ?? null,
                    'iso2' => [],
                    'iso3' => [],
                    'status' => 'unresolved',
                    'reason' => 'id_no_not_numeric',
                ];

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

            $siteId = (int) $idNoRaw;

            if (!isset($sites[$siteId])) {
                $sites[$siteId] = $this->normalizeSiteRow($row, $siteId);
            } else {
                $sites[$siteId] = $this->mergeSiteRowPreferExisting($sites[$siteId], $row);
            }

            $region = $this->normalizeRegionCode($row['region_code'] ?? null);

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

            $siteJudgements[] = [
                'index' => $i,
                'site_id' => $siteId,
                'name_en' => $row['name_en'] ?? null,
                'region_code' => $row['region_code'] ?? null,
                'iso_codes_raw' => $row['iso_codes'] ?? null,
                'iso2' => $iso2,
                'iso3' => $iso3,
                'status' => $status,
                'reason' => $reason,
            ];

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

            $imageUrls = $this->extractImageUrls($row);
            if (count($imageUrls) >= 2) {
                foreach ($imageUrls as $idx => $url) {
                    $images[] = [
                        'world_heritage_site_id' => $siteId,
                        'url' => $url,
                        'sort_order' => $idx,
                        'is_primary' => ($idx === 0) ? 1 : 0,
                    ];
                }
            }

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
                if (!isset($parties[$code3])) {
                    $parties[$code3] = [
                        'state_party_code' => $code3,
                        'name_en' => $names[$idx] ?? $names[0] ?? $code3,
                        'name_jp' => null,
                        'region' => $region,
                    ];
                } else {
                    if (($parties[$code3]['name_en'] ?? null) === $code3) {
                        $better = $names[$idx] ?? $names[0] ?? null;
                        if (is_string($better) && trim($better) !== '') {
                            $parties[$code3]['name_en'] = trim($better);
                        }
                    }
                    if (($parties[$code3]['region'] ?? null) === null && $region !== null) {
                        $parties[$code3]['region'] = $region;
                    }
                }

                $k = "{$siteId}|{$code3}";
                if (!isset($pivot[$k])) {
                    $pivot[$k] = [
                        'world_heritage_site_id' => $siteId,
                        'state_party_code' => $code3,
                        'is_primary' => ($idx === 0) ? 1 : 0,
                        'inscription_year' => isset($row['date_inscribed']) ? (int) $row['date_inscribed'] : null,
                    ];
                }
            }
        }

        if ($strict) {
            $fail = false;
            if ($invalid > 0) $fail = true;
            if ($rowsMissingId > 0) $fail = true;
            if ($rowsNonNumericId > 0) $fail = true;
            if ($rowsMissingCodes > 0) $fail = true;
            if ($rowsUnknownCodes > 0) $fail = true;

            if ($fail) {
                $this->error('Strict mode: invalid rows or unresolved country judgements exist.');
                return self::FAILURE;
            }
        }

        ksort($sites, SORT_NUMERIC);
        ksort($parties, SORT_STRING);
        ksort($pivot, SORT_STRING);

        $sitesPayload = [
            'meta' => [
                'schema' => 'world_heritage_sites.v3',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'sites' => count($sites),
                'id_standard' => 'id_no(int)',
                'region_standard' => 'region_code(EUR/AFR/APA/ARB/LAC)',
                'country_code_standard' => 'alpha-3',
                'state_party_rule' => 'ISO3 only if single-country else null',
                'image_rule' => 'primary_image_url only',
            ],
            'results' => array_values($sites),
        ];

        $partiesPayload = [
            'meta' => [
                'schema' => 'state_parties.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'state_parties' => count($parties),
                'country_code_standard' => 'alpha-3',
                'region_standard' => 'region_code(EUR/AFR/APA/ARB/LAC)',
            ],
            'results' => array_values($parties),
        ];

        $pivotPayload = [
            'meta' => [
                'schema' => 'site_state_parties.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'relations' => count($pivot),
                'country_code_standard' => 'alpha-3',
                'site_key' => 'world_heritage_site_id',
                'site_key_standard' => 'id_no(int)',
            ],
            'results' => array_values($pivot),
        ];

        $imagesPayload = [
            'meta' => [
                'schema' => 'world_heritage_site_images.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => count($results),
                'images' => count($images),
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

        $written = [
            'world_heritage_sites.json' => $sitesPayload,
            'countries.json' => $partiesPayload,
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

        $judgementsPath = $this->resolvePath($siteJudgementsOut);
        $exceptionsPath = $this->resolvePath($exceptionsOut);

        $encodedJudgements = $this->encodeJson($judgementsPayload, $pretty);
        if ($encodedJudgements === null) {
            $this->error('Failed to encode: site-country-judgements.json');
            return self::FAILURE;
        }

        $encodedExceptions = $this->encodeJson($exceptionsPayload, $pretty);
        if ($encodedExceptions === null) {
            $this->error('Failed to encode: exceptions-missing-iso-codes.json');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info("[dry] would write {$judgementsPath} (" . count($judgementsPayload['results']) . " records)");
            $this->info("[dry] would write {$exceptionsPath} (" . count($exceptionsPayload['results']) . " records)");
        } else {
            $ok = @file_put_contents($judgementsPath, $encodedJudgements);
            if ($ok === false) {
                $this->error("Failed to write: {$judgementsPath}");
                return self::FAILURE;
            }
            $this->info("Wrote {$judgementsPath} (" . count($judgementsPayload['results']) . " records)");

            $ok2 = @file_put_contents($exceptionsPath, $encodedExceptions);
            if ($ok2 === false) {
                $this->error("Failed to write: {$exceptionsPath}");
                return self::FAILURE;
            }
            $this->info("Wrote {$exceptionsPath} (" . count($exceptionsPayload['results']) . " records)");
        }

        $this->line('----');
        $this->info('Sites (unique id_no): ' . count($sites));
        $this->info('State parties (unique ISO3): ' . count($parties));
        $this->info('Site-State relations: ' . count($pivot));
        $this->info('Site images (rows): ' . count($images));
        $this->info('Site country judgements (rows): ' . count($siteJudgements));
        $this->info('Exceptions collected: ' . count($exceptions));
        $this->info("Invalid: {$invalid}");
        $this->info("Missing id_no: {$rowsMissingId}, Non-numeric id_no: {$rowsNonNumericId}");
        $this->info("Missing/empty iso_codes or iso3: {$rowsMissingCodes}, Unknown iso_codes: {$rowsUnknownCodes}");
        $this->info("Transnational rows detected (countries>=2): {$transnationalCount}");

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
                'state_parties' => count($parties),
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
            $summaryPath = $this->resolvePath($summaryFile);
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
            $name = trim((string) $v);
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

    private function normalizeSiteRow(array $row, int $siteId): array
    {
        $lat = $row['coordinates']['lat'] ?? null;
        $lon = $row['coordinates']['lon'] ?? null;

        $region = $this->normalizeRegionCode($row['region_code'] ?? null);
        $criteria = $this->extractCriteriaList($row['criteria_txt'] ?? null);

        $iso2List = $this->extractIsoCodes($row['iso_codes'] ?? null);

        $stateParty = null;
        $country = null;
        if (count($iso2List) === 1) {
            $iso3 = $this->toIso3OrNull($iso2List[0]);
            $stateParty = $iso3;
            $country = $iso3;
        }

        $imageUrls = $this->extractImageUrls($row);
        $primaryImageUrl = $imageUrls[0] ?? null;

        return [
            'id' => $siteId,
            'official_name' => $row['official_name'] ?? ($row['name_en'] ?? null),
            'name' => $row['name_en'] ?? null,
            'name_jp' => $row['name_jp'] ?? null,
            'country' => $country,
            'region' => $region,
            'state_party' => $stateParty,
            'category' => $row['category'] ?? null,
            'criteria' => $criteria,
            'year_inscribed' => isset($row['date_inscribed']) ? (int) $row['date_inscribed'] : null,
            'area_hectares' => isset($row['area_hectares']) ? (float) $row['area_hectares'] : null,
            'buffer_zone_hectares' => isset($row['buffer_zone_hectares']) ? (float) $row['buffer_zone_hectares'] : null,
            'is_endangered' => (strtolower((string) ($row['danger'] ?? 'false')) === 'true') ? 1 : 0,
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lon) ? (float) $lon : null,
            'short_description' => $row['short_description_en'] ?? null,
            'primary_image_url' => $primaryImageUrl,
            'thumbnail_image_id' => null,
            'unesco_site_url' => $row['unesco_site_url'] ?? ($row['url'] ?? null),
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
        if (($existing['state_party'] ?? null) === null) {
            $iso2List = $this->extractIsoCodes($incoming['iso_codes'] ?? null);
            if (count($iso2List) === 1) {
                $sp = $this->toIso3OrNull($iso2List[0]);
                if ($sp !== null) $existing['state_party'] = $sp;

                if (($existing['country'] ?? null) === null) {
                    $existing['country'] = $sp;
                }
            }
        }

        if (($existing['region'] ?? null) === null) {
            $region = $this->normalizeRegionCode($incoming['region_code'] ?? null);
            if ($region !== null) $existing['region'] = $region;
        }

        $fill('category', $incoming['category'] ?? null);

        if (isset($incoming['coordinates']['lat']) && ($existing['latitude'] ?? null) === null) {
            $existing['latitude'] = is_numeric($incoming['coordinates']['lat']) ? (float) $incoming['coordinates']['lat'] : null;
        }
        if (isset($incoming['coordinates']['lon']) && ($existing['longitude'] ?? null) === null) {
            $existing['longitude'] = is_numeric($incoming['coordinates']['lon']) ? (float) $incoming['coordinates']['lon'] : null;
        }

        if (($existing['short_description'] ?? null) === null && isset($incoming['short_description_en'])) {
            $existing['short_description'] = $incoming['short_description_en'];
        }

        if (($existing['primary_image_url'] ?? null) === null) {
            $urls = $this->extractImageUrls($incoming);
            $existing['primary_image_url'] = $urls[0] ?? null;
        }

        if (($existing['unesco_site_url'] ?? null) === null) {
            $u = $incoming['unesco_site_url'] ?? ($incoming['url'] ?? null);
            if ($u) $existing['unesco_site_url'] = $u;
        }

        return $existing;
    }

    private function normalizeRegionCode(mixed $v): ?string
    {
        if (!is_string($v)) return null;
        $code = strtoupper(trim($v));
        if ($code === '') return null;

        $allowed = ['EUR', 'AFR', 'APA', 'ARB', 'LAC'];
        return in_array($code, $allowed, true) ? $code : null;
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

    private function extractCriteriaList(?string $criteriaTxt): array
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
            if ((bool)$this->option('strict')) {
                throw $e;
            }
            return null;
        }
    }
}
