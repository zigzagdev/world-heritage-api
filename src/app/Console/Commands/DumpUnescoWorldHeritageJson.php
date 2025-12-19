<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DumpUnescoWorldHeritageJson extends Command
{
    protected $signature = 'world-heritage:dump-unesco
        {--country= : single country}
        {--countries= : comma-separated countries (Japan,France,Canada)}
        {--countries-file= : storage/app/... file containing country names (one per line)}
        {--all : fetch All records (no country refine)}
        {--generate-countries-file= : generate country list file (e.g. unesco/state_names.txt) from fetched results}
        {--limit=100}
        {--max=0 : 0 means no limit (per country / all)}
        {--out=unesco/whc001.json : output file for single country mode or --all}
        {--out-dir=unesco/by-country : output dir for multi country mode}
        {--pretty : pretty print JSON}
        {--dry-run : do not write files, only show counts and validation}';

    protected $description = 'Fetch UNESCO dataset (whc001) and dump raw responses to storage/app as JSON (multi-country, --all, dry-run, and country list generation)';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $max = (int) $this->option('max');
        $pretty = (bool) $this->option('pretty');
        $dryRun = (bool) $this->option('dry-run');
        $allMode = (bool) $this->option('all');
        $countriesFileOut = trim((string) $this->option('generate-countries-file'));

        $baseUrl = 'https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/records';

        if ($allMode) {
            if ($this->option('country') || $this->option('countries') || $this->option('countries-file')) {
                $this->warn('Ignoring --country/--countries/--countries-file because --all was specified.');
            }

            $outPath = (string) $this->option('out');

            $result = $this->dumpAll(
                baseUrl: $baseUrl,
                limit: $limit,
                max: $max,
                outPath: $outPath,
                pretty: $pretty,
                dryRun: $dryRun,
            );

            if ($result['ok'] === false) return 1;

            if ($countriesFileOut !== '') {
                $this->generateCountriesFileFromResults(
                    resultsAll: $result['results'],
                    outPath: $countriesFileOut,
                    dryRun: $dryRun
                );
            }

            return 0;
        }

        $countries = $this->resolveCountries();
        if ($countries === []) {
            $this->error('No countries provided. Use --country / --countries / --countries-file, or use --all');
            return 1;
        }

        if (count($countries) === 1) {
            $country = $countries[0];
            $outPath = (string) $this->option('out');

            $res = $this->dumpOneCountry(
                baseUrl: $baseUrl,
                country: $country,
                limit: $limit,
                max: $max,
                outPath: $outPath,
                pretty: $pretty,
                dryRun: $dryRun,
            );

            return $res;
        }

        // Multi country
        $outDir = (string) $this->option('out-dir');
        $ok = 0;
        $ng = 0;

        foreach ($countries as $country) {
            $safe = $this->slugifyCountry($country);
            $outPath = rtrim($outDir, '/') . "/{$safe}.json";

            $this->line('----');
            $this->info("Target country: {$country}");
            $this->line($dryRun
                ? "Dry-run: will NOT write file (planned path: storage/app/{$outPath})"
                : "Will write: storage/app/{$outPath}"
            );

            $code = $this->dumpOneCountry(
                baseUrl: $baseUrl,
                country: $country,
                limit: $limit,
                max: $max,
                outPath: $outPath,
                pretty: $pretty,
                dryRun: $dryRun,
            );

            if ($code === 0) $ok++;
            else $ng++;
        }

        $this->info("Done. ok={$ok}, ng={$ng}");
        return $ng > 0 ? 1 : 0;
    }

    /**
     * Dump all records (no refine).
     *
     * @return array{ok: bool, results: array<int, array<string, mixed>>}
     */
    private function dumpAll(string $baseUrl, int $limit, int $max, string $outPath, bool $pretty, bool $dryRun): array
    {
        $this->info('Fetching All records (no country refine).');

        $first = $this->fetch($baseUrl, null, 1, 0);
        if ($first === null) return ['ok' => false, 'results' => []];

        $total = (int) ($first['total_count'] ?? 0);
        if ($total <= 0) {
            $this->error('No records returned (total_count=0) in --all mode');
            return ['ok' => false, 'results' => []];
        }

        $this->info("Total count (ALL): {$total}");

        $offset = 0;
        $fetched = 0;
        $resultsAll = [];

        while (true) {
            if ($max > 0 && $fetched >= $max) break;

            $resp = $this->fetch($baseUrl, null, $limit, $offset);
            if ($resp === null) return ['ok' => false, 'results' => []];

            $results = $resp['results'] ?? null;
            if (!is_array($results) || $results === []) break;

            foreach ($results as $row) {
                if (!is_array($row)) continue;
                $resultsAll[] = $row;
                $fetched++;
                if ($max > 0 && $fetched >= $max) break 2;
            }

            $offset += count($results);
        }

        if ($max <= 0 && $fetched !== $total) {
            $this->warn("Fetched count mismatch (ALL): fetched={$fetched}, total_count={$total}");
            $this->warn('This may indicate pagination issues or data changes on the upstream side.');
        } else {
            $this->info("Fetched {$fetched} records (ALL)");
        }

        if ($dryRun) {
            $this->info('Dry-run enabled: skipping JSON write.');
            return ['ok' => true, 'results' => $resultsAll];
        }

        $payload = [
            'meta' => [
                'dataset' => 'whc001',
                'scope' => 'ALL',
                'fetched' => $fetched,
                'total_count' => $total,
                'dumped_at' => now()->toIso8601String(),
                'source' => $baseUrl,
            ],
            'results' => $resultsAll,
        ];

        $json = $pretty
            ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            $this->error('Failed to encode JSON');
            return ['ok' => false, 'results' => []];
        }

        Storage::disk('local')->put($outPath, $json);
        $this->info("Dumped {$fetched} records to storage/app/{$outPath}");

        return ['ok' => true, 'results' => $resultsAll];
    }

    private function dumpOneCountry(
        string $baseUrl,
        string $country,
        int $limit,
        int $max,
        string $outPath,
        bool $pretty,
        bool $dryRun
    ): int {
        $country = trim($country);
        if ($country === '') {
            $this->error('Empty country name');
            return 1;
        }

        $first = $this->fetch($baseUrl, $country, 1, 0);
        if ($first === null) return 1;

        $total = (int) ($first['total_count'] ?? 0);
        if ($total <= 0) {
            $this->error("No records returned (total_count=0) for country={$country}");
            return 1;
        }

        $this->info("Total count for {$country}: {$total}");

        $offset = 0;
        $fetched = 0;
        $resultsAll = [];

        while (true) {
            if ($max > 0 && $fetched >= $max) break;

            $resp = $this->fetch($baseUrl, $country, $limit, $offset);
            if ($resp === null) return 1;

            $results = $resp['results'] ?? null;
            if (!is_array($results) || $results === []) break;

            foreach ($results as $row) {
                if (!is_array($row)) continue;

                $resultsAll[] = $row;
                $fetched++;

                if ($max > 0 && $fetched >= $max) break 2;
            }

            $offset += count($results);
        }

        if ($max <= 0 && $fetched !== $total) {
            $this->warn("Fetched count mismatch for {$country}: fetched={$fetched}, total_count={$total}");
            $this->warn('This may indicate pagination issues or data changes on the upstream side.');
        } else {
            $this->info("Fetched {$fetched} records for {$country}");
        }

        if ($dryRun) {
            return 0;
        }

        $payload = [
            'meta' => [
                'dataset' => 'whc001',
                'country' => $country,
                'fetched' => $fetched,
                'total_count' => $total,
                'dumped_at' => now()->toIso8601String(),
                'source' => $baseUrl,
            ],
            'results' => $resultsAll,
        ];

        $json = $pretty
            ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            $this->error('Failed to encode JSON');
            return 1;
        }

        Storage::disk('local')->put($outPath, $json);
        $this->info("Dumped {$fetched} records to storage/app/{$outPath}");

        return 0;
    }

    private function generateCountriesFileFromResults(array $resultsAll, string $outPath, bool $dryRun): void
    {
        $set = [];

        foreach ($resultsAll as $row) {
            $states = $row['states_names'] ?? null;
            if (!is_array($states)) continue;

            foreach ($states as $name) {
                $name = trim((string) $name);
                if ($name === '') continue;
                $set[$name] = true;
            }
        }

        $countries = array_keys($set);
        sort($countries, SORT_STRING);
        $this->info('Countries extracted from results: ' . count($countries));

        $preview = array_slice($countries, 0, 15);
        if ($preview !== []) {
            $this->line('Preview: ' . implode(', ', $preview) . (count($countries) > 15 ? ', ...' : ''));
        }

        if ($dryRun) {
            $this->info("Dry-run enabled: skipping countries-file write (planned path: storage/app/{$outPath}).");
            return;
        }

        $content = implode("\n", $countries) . "\n";
        Storage::disk('local')->put($outPath, $content);
        $this->info("Generated countries file: storage/app/{$outPath}");
    }

    private function fetch(string $baseUrl, ?string $country, int $limit, int $offset): ?array
    {
        $query = [
            'limit'  => $limit,
            'offset' => $offset,
        ];

        if ($country !== null && trim($country) !== '') {
            $query['refine'] = 'states_names:"' . $country . '"';
        }

        $res = Http::retry(3, 500)
            ->acceptJson()
            ->get($baseUrl, $query);

        if (!$res->ok()) {
            $this->error("UNESCO API error: HTTP {$res->status()} offset={$offset}" . ($country ? " country={$country}" : ''));
            return null;
        }

        $json = $res->json();
        if (!is_array($json)) {
            $this->error("UNESCO API invalid JSON offset={$offset}" . ($country ? " country={$country}" : ''));
            return null;
        }

        return $json;
    }

    private function resolveCountries(): array
    {
        $single = trim((string) $this->option('country'));
        $csv = trim((string) $this->option('countries'));
        $file = trim((string) $this->option('countries-file'));

        if ($csv !== '') {
            $items = array_map('trim', explode(',', $csv));
            return array_values(array_filter(array_unique($items)));
        }

        if ($file !== '') {
            if (!Storage::disk('local')->exists($file)) {
                $this->error("countries-file not found: storage/app/{$file}");
                return [];
            }
            $lines = preg_split('/\R/u', (string) Storage::disk('local')->get($file)) ?: [];
            $items = array_map(fn($v) => trim((string) $v), $lines);
            return array_values(array_filter(array_unique($items)));
        }

        return $single !== '' ? [$single] : [];
    }

    private function slugifyCountry(string $country): string
    {
        $s = strtolower(trim($country));
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? $s;
        $s = trim($s, '-');
        return $s !== '' ? $s : 'unknown';
    }
}
