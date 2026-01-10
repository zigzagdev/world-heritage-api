<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SplitWorldHeritageImageJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:split-image-json
        {--in= : Input raw JSON file (e.g. storage/app/private/unesco/world-heritage-sites.json)}
        {--out=unesco/normalized/world_heritage_site_images.json : Output JSON path in storage/app/...}
        {--pretty : Pretty print JSON}
        {--dry-run : Do not write file (only logs)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Split/normalize UNESCO raw JSON into import-ready JSON for world_heritage_site_images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $in = trim((string)$this->option('in'));
        $out = trim((string)$this->option('out'));
        $pretty = (bool)$this->option('pretty');
        $dryRun = (bool)$this->option('dry-run');

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

        $results = $json['results'] ?? null;
        if (!is_array($results)) {
            $this->error('Invalid raw format: expected {"results":[...]}');
            return self::FAILURE;
        }

        $images = [];
        $scanned = 0;
        $skippedNoId = 0;
        $skippedNoImages = 0;

        foreach ($results as $row) {
            $scanned++;
            if (!is_array($row)) {
                $skippedNoId++;
                continue;
            }

            $idNoRaw = trim((string)($row['id_no'] ?? ($row['id'] ?? '')));
            if ($idNoRaw === '' || !is_numeric($idNoRaw)) {
                $skippedNoId++;
                continue;
            }
            $siteId = (int)$idNoRaw;

            $urls = $this->extractImageUrlsPreferImagesUrls($row);
            if ($urls === []) {
                $skippedNoImages++;
                continue;
            }

            foreach ($urls as $idx => $url) {
                $images[] = [
                    'world_heritage_site_id' => $siteId,
                    'url' => $url,
                    'url_hash' => hash('sha256', $url),
                    'sort_order' => $idx,
                    'is_primary' => $idx === 0 ? 1 : 0,
                ];
            }
        }

        $payload = [
            'meta' => [
                'schema' => 'world_heritage_site_images.import.v1',
                'source_raw' => $in,
                'generated_at' => now()->toIso8601String(),
                'rows_scanned' => $scanned,
                'images' => count($images),
                'skipped_no_id' => $skippedNoId,
                'skipped_no_images' => $skippedNoImages,
                'target_table' => 'world_heritage_site_images',
                'rule' => 'prefer images_urls; fallback main_image_url.url',
            ],
            'results' => $images,
        ];

        $encoded = $this->encodeJson($payload, $pretty);
        if ($encoded === null) {
            $this->error('Failed to encode output JSON');
            return self::FAILURE;
        }

        $outPath = $this->resolvePathToFile($out);

        if ($dryRun) {
            $this->info("[dry] would write: {$outPath}");
            $this->info("scanned={$scanned} images=" . count($images) . " skipped_no_id={$skippedNoId} skipped_no_images={$skippedNoImages}");
            return self::SUCCESS;
        }

        $dir = dirname($outPath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                $this->error("Failed to create output dir: {$dir}");
                return self::FAILURE;
            }
        }

        if (@file_put_contents($outPath, $encoded) === false) {
            $this->error("Failed to write: {$outPath}");
            return self::FAILURE;
        }

        $this->info("Wrote {$outPath} (" . count($images) . " records)");
        $this->info("scanned={$scanned} skipped_no_id={$skippedNoId} skipped_no_images={$skippedNoImages}");
        return self::SUCCESS;
    }

    private function extractImageUrlsPreferImagesUrls(array $row): array
    {
        $urls = [];
        $images = $row['images_urls'] ?? null;

        if (is_array($images) && $images !== []) {
            foreach ($images as $p) {
                if (!is_string($p)) continue;
                $p = trim($p);
                if ($p !== '') $urls[] = $p;
            }
        } elseif (is_string($images)) {
            $parts = preg_split('/\s*,\s*/', trim($images)) ?: [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') $urls[] = $p;
            }
        }

        if ($urls === []) {
            $main = $row['main_image_url']['url'] ?? null;
            if (is_string($main)) {
                $main = trim($main);
                if ($main !== '') $urls[] = $main;
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
}
