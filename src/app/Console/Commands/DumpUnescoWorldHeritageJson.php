<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DumpUnescoWorldHeritageJson extends Command
{
    protected $signature = 'world-heritage:dump-unesco
        {--country= : Optional refine filter (exact match for states_names)}
        {--all : Fetch ALL records (no refine). If set, --country is ignored}
        {--limit=100 : Page size}
        {--max=0 : 0 means no limit}
        {--out=unesco/whc001.json : Storage path (storage/app/...)}
        {--pretty : Pretty print JSON}
        {--dry-run : Do not write file, only show counts and validation}';

    protected $description = 'Fetch UNESCO dataset (whc001) and dump raw responses to a single JSON file (immutable source of truth)';

    public function handle(): int
    {
        $limit  = max(1, (int) $this->option('limit'));
        $max    = (int) $this->option('max');
        $pretty = (bool) $this->option('pretty');
        $dryRun = (bool) $this->option('dry-run');
        $all    = (bool) $this->option('all');

        $country = trim((string) $this->option('country'));
        if ($all && $country !== '') {
            $this->warn('Ignoring --country because --all was specified.');
            $country = '';
        }

        // どっちも未指定の場合は事故りやすいので明示（好みで OK）
        if (!$all && $country === '') {
            $this->error('Specify either --all or --country=<states_names exact match>.');
            return 1;
        }

        $outPath = (string) $this->option('out');
        $baseUrl = 'https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/records';

        $modeLabel = $all ? 'ALL' : "country={$country}";
        $this->info("Mode: {$modeLabel}");
        $this->info("Output: storage/app/{$outPath}");
        if ($dryRun) $this->info('Dry-run: file will NOT be written.');

        // total_count を掴む（limit=1）
        $first = $this->fetch($baseUrl, $all ? null : $country, 1, 0);
        if ($first === null) return 1;

        $total = (int) ($first['total_count'] ?? 0);
        if ($total <= 0) {
            $this->error("No records returned (total_count=0) for {$modeLabel}");
            return 1;
        }

        $this->info("Total count: {$total}");

        $offset = 0;
        $fetched = 0;
        $resultsAll = [];

        while (true) {
            if ($max > 0 && $fetched >= $max) break;

            $resp = $this->fetch($baseUrl, $all ? null : $country, $limit, $offset);
            if ($resp === null) return 1;

            $results = $resp['results'] ?? null;
            if (!is_array($results) || $results === []) break;

            foreach ($results as $row) {
                if (!is_array($row)) continue;

                $resultsAll[] = $row;
                $fetched++;

                if ($max > 0 && $fetched >= $max) break 2;
            }

            // API返却件数ベースで進める
            $offset += count($results);
        }

        // max無しのときだけ整合性チェック
        if ($max <= 0 && $fetched !== $total) {
            $this->warn("Fetched count mismatch: fetched={$fetched}, total_count={$total}");
            $this->warn('Upstream data may have changed during paging, or pagination may be unstable.');
        } else {
            $this->info("Fetched: {$fetched}");
        }

        if ($dryRun) {
            return 0;
        }

        $payload = [
            'meta' => [
                'dataset' => 'whc001',
                'mode' => $all ? 'ALL' : 'FILTERED',
                'filter' => $all ? null : ['states_names' => $country],
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

    private function fetch(string $baseUrl, ?string $country, int $limit, int $offset): ?array
    {
        $query = [
            'limit'  => $limit,
            'offset' => $offset,
        ];

        // Optional refine filter
        if ($country !== null && trim($country) !== '') {
            $query['refine'] = 'states_names:"' . $country . '"';
        }

        $res = Http::retry(3, 500)
            ->acceptJson()
            ->get($baseUrl, $query);

        if (!$res->ok()) {
            $suffix = $country ? " country={$country}" : '';
            $this->error("UNESCO API error: HTTP {$res->status()} offset={$offset}{$suffix}");
            return null;
        }

        $json = $res->json();
        if (!is_array($json)) {
            $suffix = $country ? " country={$country}" : '';
            $this->error("UNESCO API invalid JSON offset={$offset}{$suffix}");
            return null;
        }

        return $json;
    }
}
