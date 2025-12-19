<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SplitWorldHeritageJson extends Command
{
    protected $signature = 'world-heritage:split-json
        {--in= : Input raw JSON file (e.g. storage/app/unesco/raw/whc001-all.json)}
        {--out=storage/app/unesco/by-states-names : Output directory (relative to project root)}
        {--pretty : pretty print JSON}
        {--log-limit=50 : Max number of skipped/invalid log lines}
        {--summary-file= : Optional summary JSON file path (e.g. storage/app/unesco/split-summary.json)}
        {--strict : Fail if any skipped/invalid rows exist}
        {--clean : Delete existing *.json in output dir before writing}
        {--dry-run : Do not write files (only logs/summary)}';

    protected $description = 'Split raw UNESCO JSON (meta+results) into states_names-based JSON files (no DB)';

    public function handle(): int
    {
        $in          = (string) $this->option('in');
        $out         = (string) $this->option('out');
        $pretty      = (bool) $this->option('pretty');
        $logLimit    = max(0, (int) $this->option('log-limit'));
        $summaryFile = trim((string) $this->option('summary-file'));
        $strict      = (bool) $this->option('strict');
        $clean       = (bool) $this->option('clean');
        $dryRun      = (bool) $this->option('dry-run');

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

        $logged = 0;
        $logSkip = function (string $reason, int $index, mixed $idNo = null, array $extra = []) use ($logLimit, &$logged): void {
            if ($logLimit <= 0) return;
            if ($logged >= $logLimit) return;

            $idPart = ($idNo !== null && $idNo !== '') ? " id_no={$idNo}" : '';
            $extraPart = $extra !== [] ? ' extra=' . json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $this->warn("[skip] index={$index}{$idPart} reason={$reason}{$extraPart}");
            $logged++;
        };

        $grouped = [];

        $skipped = 0;
        $invalid = 0;
        $transnationalCount = 0;
        $transnationalExamples = [];
        $transnationalExampleLimit = 25;

        foreach ($results as $i => $row) {
            $i = (int) $i;

            if (!is_array($row)) {
                $invalid++;
                $skipped++;
                $logSkip('row_not_object', $i, null);
                continue;
            }

            $idNo = $row['id_no'] ?? ($row['id'] ?? null);

            $statesNames = $row['states_names'] ?? null;
            if (!is_array($statesNames) || $statesNames === []) {
                $skipped++;
                $logSkip('states_names_missing_or_empty', $i, $idNo);
                continue;
            }

            $validNames = $this->normalizeStatesNames($statesNames);

            if ($validNames === []) {
                $skipped++;
                $logSkip('states_names_all_blank_or_invalid', $i, $idNo);
                continue;
            }

            if (count($validNames) > 1) {
                $transnationalCount++;
                if (count($transnationalExamples) < $transnationalExampleLimit) {
                    $transnationalExamples[] = [
                        'index' => $i,
                        'id_no' => $idNo,
                        'states_names' => $validNames,
                    ];
                }
            }

            foreach ($validNames as $displayName) {
                $key = $this->fileKey($displayName);

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'display' => $displayName,
                        'rows' => [],
                        'stats' => ['records' => 0],
                    ];
                }

                $grouped[$key]['rows'][] = $row;
                $grouped[$key]['stats']['records']++;
            }
        }

        if ($grouped === []) {
            $this->error('No rows could be grouped by states_names');
            return self::FAILURE;
        }

        ksort($grouped, SORT_STRING);

        $writtenFiles = 0;
        $writtenDistributedRows = 0;
        $distinctGroups = count($grouped);

        foreach ($grouped as $key => $bundle) {
            $displayName = (string) ($bundle['display'] ?? '');
            $rows = $bundle['rows'] ?? [];
            if (!is_array($rows)) $rows = [];

            $payload = $this->buildPayload(
                inOptionPath: $in,
                upstreamMeta: $meta,
                statesName: $displayName,
                key: $key,
                records: count($rows),
                results: $rows
            );

            $encoded = $this->encodeJson($payload, $pretty);
            if ($encoded === null) {
                $this->warn("Failed to encode JSON for {$displayName} ({$key}), skipped");
                continue;
            }

            $filePath = rtrim($outDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$key}.json";

            if (!$dryRun) {
                $ok = @file_put_contents($filePath, $encoded);
                if ($ok === false) {
                    $this->warn("Failed to write: {$filePath}");
                    continue;
                }
            }

            $writtenFiles++;
            $writtenDistributedRows += count($rows);

            $this->info(($dryRun ? '[dry] would write ' : 'Wrote ') . "{$filePath} (" . count($rows) . " records)");
        }

        $this->line('----');
        $this->info("Groups (states_names): {$distinctGroups}");
        $this->info("Written files: {$writtenFiles}");
        $this->info("Distributed records (sum of group sizes): {$writtenDistributedRows}");
        $this->info("Skipped: {$skipped}, Invalid: {$invalid}");
        $this->info("Transnational rows detected (states_names>=2): {$transnationalCount}");

        if ($logLimit > 0 && $logged >= $logLimit) {
            $this->warn("Skip logs truncated (log-limit={$logLimit})");
        }

        $summary = [
            'meta' => [
                'source_raw' => $in,
                'input_path_resolved' => $inPath,
                'output_dir_resolved' => $outDir,
                'split_at' => now()->toIso8601String(),
                'group_by' => 'states_names',
                'dry_run' => $dryRun,
                'clean' => $clean,
                'strict' => $strict,
            ],
            'counts' => [
                'input_rows' => count($results),
                'groups' => $distinctGroups,
                'written_files' => $writtenFiles,
                'distributed_records' => $writtenDistributedRows,
                'skipped' => $skipped,
                'invalid' => $invalid,
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

        if ($strict && ($skipped > 0 || $invalid > 0)) {
            $this->error('Strict mode: skipped/invalid rows exist, failing.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function buildPayload(
        string $inOptionPath,
        array $upstreamMeta,
        string $statesName,
        string $key,
        int $records,
        array $results
    ): array {
        return [
            'meta' => [
                'source_raw' => $inOptionPath,
                'group_by' => 'states_names',
                'states_name' => $statesName,
                'file_key' => $key,
                'records' => $records,
                'split_at' => now()->toIso8601String(),
                'upstream_meta' => $upstreamMeta,
            ],
            'results' => $results,
        ];
    }

    private function normalizeStatesNames(array $statesNames): array
    {
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

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function fileKey(string $displayName): string
    {
        $slug = $this->slugify($displayName);
        $hash = substr(sha1($displayName), 0, 8);
        return "{$slug}__{$hash}";
    }

    private function slugify(string $name): string
    {
        $s = mb_strtolower(trim($name), 'UTF-8');
        $s = preg_replace('/\s+/u', '_', $s) ?? $s;
        $s = preg_replace('/[^a-z0-9_\-]/', '', $s) ?? $s;

        return $s !== '' ? $s : 'unknown';
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
}
