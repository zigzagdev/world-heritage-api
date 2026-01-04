<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;


class DumpWorldHeritageSiteJapaneseName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:dump-site-names-ja
        {--in=storage/app/private/unesco/world-heritage-sites.json : Input raw dump JSON (meta + results[]) containing id_no}
        {--out=storage/app/private/unesco/site_names_ja_dump.json : Output JSON array [{id_no, name_jp}, ...]}
        {--exceptions-out=storage/app/private/unesco/site_names_ja_exceptions.json : Output exceptions JSON array}
        {--base-url=https://whc.unesco.org/ja/list : Base URL}
        {--sleep-ms=800 : Sleep milliseconds between requests}
        {--timeout=12 : HTTP timeout seconds}
        {--max=0 : Max sites to fetch (0 = no limit)}
        {--resume : Resume by reading existing --out (skip already resolved ids)}
        {--pretty : Pretty print JSON}
        {--dry-run : Do not fetch/write}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump UNESCO Japanese site names as JSON rows: [{id_no, name_jp}] by fetching whc.unesco.org/ja/list/{id_no}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $in = (string) $this->option('in');
        $out = (string) $this->option('out');
        $exceptionsOut = (string) $this->option('exceptions-out');

        $baseUrl = rtrim((string) $this->option('base-url'), '/');
        $sleepMs = max(0, (int) $this->option('sleep-ms'));
        $timeout = max(1, (int) $this->option('timeout'));
        $max = (int) $this->option('max');
        $resume = (bool) $this->option('resume');
        $pretty = (bool) $this->option('pretty');
        $dryRun = (bool) $this->option('dry-run');

        $inPath = $this->resolvePath($in);
        if (!is_file($inPath)) {
            $this->error("Input not found: {$inPath}");
            return self::FAILURE;
        }

        $raw = @file_get_contents($inPath);
        if ($raw === false) {
            $this->error("Failed to read input: {$inPath}");
            return self::FAILURE;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $this->error("Invalid JSON: {$inPath}");
            return self::FAILURE;
        }

        $results = $json['results'] ?? null;
        if (!is_array($results) || $results === []) {
            $this->error('Invalid raw format: expected {"results":[...]} with non-empty results');
            return self::FAILURE;
        }

        $ids = $this->extractIds($results);
        if ($ids === []) {
            $this->error('No valid numeric id_no found.');
            return self::FAILURE;
        }

        $this->info("IDs detected: " . count($ids));

        $existingRows = [];
        $existingMap = [];
        if ($resume) {
            $existingRows = $this->loadExistingRows($out);
            foreach ($existingRows as $r) {
                $id = (string)($r['id_no'] ?? '');
                $nm = (string)($r['name_jp'] ?? '');
                if ($id !== '' && $nm !== '') $existingMap[$id] = true;
            }
            $this->info("Resume enabled: existing rows=" . count($existingRows));
        }

        $targets = [];
        foreach ($ids as $id) {
            $sid = (string)$id;
            if ($resume && isset($existingMap[$sid])) continue;
            $targets[] = $id;
            if ($max > 0 && count($targets) >= $max) break;
        }

        $this->info("Targets to fetch: " . count($targets) . ($max > 0 ? " (max={$max})" : ''));

        if ($dryRun) {
            $this->warn('Dry-run enabled: will not fetch/write.');
            return self::SUCCESS;
        }

        $rows = $existingRows;
        $exceptions = [];

        foreach ($targets as $i => $id) {
            $sid = (string)$id;
            $url = "{$baseUrl}/{$sid}";

            $this->line("[$i/" . (count($targets) - 1) . "] {$url}");

            $html = $this->fetchHtml($url, $timeout);
            if ($html === null) {
                $exceptions[] = $this->ex($sid, $url, 'fetch_failed');
                $this->warn("  -> fetch_failed");
                $this->sleepMs($sleepMs);
                continue;
            }

            $name = $this->extractJapaneseTitle($html);
            $name = $this->cleanupTitle($name ?? '');

            if ($name === '') {
                $exceptions[] = $this->ex($sid, $url, 'parse_failed');
                continue;
            }

            if (!$this->containsJapanese($name)) {
                $exceptions[] = $this->ex($sid, $url, 'no_japanese_label');
                continue;
            }


            $rows[] = [
                'id_no' => (int)$id,
                'name_jp' => $name,
            ];

            $this->info("  -> ok: {$name}");
            $this->sleepMs($sleepMs);
        }

        $debug = [];
        foreach ($rows as $r) {
            if (!is_array($r)) continue;
            if (!isset($r['id_no'])) continue;
            $id = (string)$r['id_no'];
            $nm = trim((string)($r['name_jp'] ?? ''));
            if ($id === '' || $nm === '') continue;
            $debug[$id] = ['id_no' => (int)$id, 'name_jp' => $nm];
        }
        ksort($debug, SORT_NUMERIC);
        $rows = array_values($debug);

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $flags |= JSON_PRETTY_PRINT;

        $outJson = json_encode($rows, $flags);
        $exJson  = json_encode($exceptions, $flags);

        if ($outJson === false || $exJson === false) {
            $this->error('Failed to encode JSON.');
            return self::FAILURE;
        }

        Storage::disk('local')->put($this->toLocalDiskPath($out), $outJson);
        Storage::disk('local')->put($this->toLocalDiskPath($exceptionsOut), $exJson);

        $this->info("Wrote rows: storage/app/" . $this->toLocalDiskPath($out) . " (count=" . count($rows) . ")");
        $this->info("Wrote exceptions: storage/app/" . $this->toLocalDiskPath($exceptionsOut) . " (count=" . count($exceptions) . ")");

        return self::SUCCESS;
    }

    private function extractIds(array $results): array
    {
        $seen = [];
        $ids = [];
        foreach ($results as $row) {
            if (!is_array($row)) continue;
            $idRaw = trim((string)($row['id_no'] ?? ($row['id'] ?? '')));
            if ($idRaw === '') continue;
            if (!ctype_digit($idRaw)) continue;
            $id = (int)$idRaw;
            if ($id <= 0) continue;
            if (!isset($seen[$id])) {
                $seen[$id] = true;
                $ids[] = $id;
            }
        }
        sort($ids, SORT_NUMERIC);
        return $ids;
    }

    private function loadExistingRows(string $out): array
    {
        $p = $this->toLocalDiskPath($out);
        if (!Storage::disk('local')->exists($p)) return [];
        $c = (string) Storage::disk('local')->get($p);
        $j = json_decode($c, true);
        if (!is_array($j)) return [];

        return array_values(array_filter($j, fn($v) => is_array($v)));
    }
    private function containsJapanese(string $s): bool
    {
        return preg_match('/[\p{Hiragana}\p{Katakana}\p{Han}]/u', $s) === 1;
    }

    private function fetchHtml(string $url, int $timeout): ?string
    {
        $res = Http::retry(2, 500)
            ->timeout($timeout)
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'ja,en;q=0.7',
                'User-Agent' => 'WorldHeritageDataBot/1.0 (offline dump)',
            ])
            ->get($url);

        if (!$res->ok()) return null;

        $body = $res->body();
        return is_string($body) && $body !== '' ? $body : null;
    }

    private function extractJapaneseTitle(string $html): ?string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $loaded = $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$loaded) return null;

        $xp = new DOMXPath($doc);

        $h1 = $xp->query('//main//h1');
        if ($h1 && $h1->length > 0) {
            $t = trim((string)$h1->item(0)?->textContent);
            if ($t !== '') return $t;
        }

        $h1 = $xp->query('//h1');
        if ($h1 && $h1->length > 0) {
            $t = trim((string) $h1->item(0)?->textContent);
            if ($t !== '') return $t;
        }

        $og = $xp->query('//meta[@property="og:title"]/@content');
        if ($og && $og->length > 0) {
            $t = trim((string)$og->item(0)?->nodeValue);
            if ($t !== '') return $t;
        }

        $title = $xp->query('//title');
        if ($title && $title->length > 0) {
            $t = trim((string)$title->item(0)?->textContent);
            if ($t !== '') return $t;
        }

        return null;
    }

    private function cleanupTitle(string $title): string
    {
        $t = trim($title);
        if ($t === '') return '';

        $t = str_replace('UNESCO World Heritage Centre', '', $t);
        $t = preg_replace('/\s*[|｜]\s*$/u', '', $t) ?? $t;
        $t = preg_replace('/\s*[-–—]\s*$/u', '', $t) ?? $t;

        return trim($t);
    }

    private function ex(string $siteId, string $url, string $reason): array
    {
        return [
            'site_id' => $siteId,
            'url' => $url,
            'reason' => $reason,
            'at' => now()->toIso8601String(),
        ];
    }

    private function sleepMs(int $ms): void
    {
        if ($ms <= 0) return;
        usleep($ms * 1000);
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;
        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;
        if (str_starts_with($path, 'storage/app/')) return base_path($path);
        return base_path($path);
    }

    private function toLocalDiskPath(string $path): string
    {
        $trimPath = trim($path);
        if ($trimPath === '') return $trimPath;
        if (str_starts_with($trimPath, 'storage/app/')) {
            return substr($trimPath, strlen('storage/app/'));
        }

        return ltrim($trimPath, '/');
    }
}
